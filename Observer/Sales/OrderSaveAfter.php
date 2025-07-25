<?php
/**
 * @package Koin\Payment
 * @copyright Copyright (c) 2021 Koin
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Koin\Payment\Observer\Sales;

use Koin\Payment\Helper\Antifraud;
use Koin\Payment\Helper\Data;
use Koin\Payment\Helper\Order as HelperOrder;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

class OrderSaveAfter implements ObserverInterface
{
    /** @var Data  */
    protected $helper;

    /** @var Antifraud  */
    protected $helperAntifraud;

    /** @var HelperOrder  */
    protected $helperOrder;

    /**
     * @param Data $helper
     * @param Antifraud $helperAntifraud
     * @param HelperOrder $helperOrder
     */
    public function __construct(
        Data $helper,
        Antifraud $helperAntifraud,
        HelperOrder $helperOrder
    ) {
        $this->helper = $helper;
        $this->helperAntifraud = $helperAntifraud;
        $this->helperOrder = $helperOrder;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getDataObject();
        $originalState = $order->getOrigData('state');

        try {
            if ($originalState != $order->getState()) {
                $this->notifyOrder($order);
                $this->notifyAntifraud($order);
            }

            $this->addToQueue($order);

        } catch (\Exception $e) {
            $this->helper->log($e->getMessage());
        }
    }

    /**
     * @param Order $order
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function addToQueue($order)
    {
        if ($this->helper->getAntifraudConfig('active')) {
            $paymentMethods = explode(',', $this->helper->getAntifraudConfig('payment_methods'));
            $statuses = explode(',', $this->helper->getAntifraudConfig('order_status'));
            $minOrderTotal = (float) $this->helper->getAntifraudConfig('min_order_total');

            //@phpstan-ignore-next-line
            if (!empty($paymentMethods)) {
                if (in_array($order->getPayment()->getMethod(), $paymentMethods)) {
                    if (in_array($order->getStatus(), $statuses)) {
                        if ($order->getGrandTotal() >= $minOrderTotal) {
                            $this->helperAntifraud->addToQueue($order, $order->getData('koin_antifraud_fingerprint'));
                        }
                    }
                }
            }
        }
    }

    private function getNotificationStatusForState(Order $order): string
    {
        $state = $order->getState();
        switch ($state) {
            case Order::STATE_COMPLETE:
                return 'FINALIZED';
            case Order::STATE_CLOSED:
                return 'REFUNDED';
            case Order::STATE_CANCELED:
                return 'CANCELLED';
            case Order::STATE_PROCESSING:
                // Check if order is fully paid by comparing total invoiced with grand total
                // or if total due is zero (meaning no payment is due - order is paid)
                if ($order->getTotalInvoiced() >= $order->getGrandTotal() || $order->getTotalDue() == 0) {
                    return 'COLLECTED';
                }
                return 'AUTHORIZED';
            default:
                return '';
        }
    }

    /**
     * @param Order $order
     * @return void
     */
    public function notifyOrder(Order $order): void
    {
        if ($order->getPayment()->getMethod() == \Koin\Payment\Model\Ui\CreditCard\ConfigProvider::CODE) {
            $notificationStatus = $this->getNotificationStatusForState($order);

            if ($notificationStatus !== '') {
                $this->helperOrder->notification(
                    $order,
                    $notificationStatus
                );
            }
        }
    }

    public function notifyAntifraud(Order $order): void
    {
        if ($this->helper->getAntifraudConfig('active')) {
            $notificationStatus = $this->getNotificationStatusForState($order);
            $afStatus = $order->getPayment()->getAdditionalInformation('koin_antifraud_notification_status') ?? '';

            if ($notificationStatus !== '' && $notificationStatus !== $afStatus) {
                try {
                    $this->helperAntifraud->notification($order, $notificationStatus);
                    $order->getPayment()->setAdditionalInformation(
                        'koin_antifraud_notification_status',
                        $notificationStatus
                    );
                } catch (\Exception $e) {
                    $this->helper->log('Failed to send antifraud notification: ' . $e->getMessage());
                }
            }
        }
    }
}
