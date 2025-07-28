<?php
/**
 * @package Koin\Payment
 * @copyright Copyright (c) 2021 Koin
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Koin\Payment\Service;

use Koin\Payment\Helper\Antifraud;
use Koin\Payment\Helper\Data;
use Koin\Payment\Helper\Order as HelperOrder;
use Koin\Payment\Model\Ui\CreditCard\ConfigProvider;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class NotificationService
{
    /** @var Data  */
    private $helper;

    /** @var Antifraud  */
    private $helperAntifraud;

    /** @var HelperOrder  */
    private $helperOrder;

    /** @var OrderRepositoryInterface  */
    private $orderRepository;

    /**
     * @param Data $helper
     * @param Antifraud $helperAntifraud
     * @param HelperOrder $helperOrder
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Data $helper,
        Antifraud $helperAntifraud,
        HelperOrder $helperOrder,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->helper = $helper;
        $this->helperAntifraud = $helperAntifraud;
        $this->helperOrder = $helperOrder;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Send notifications if status has changed
     *
     * @param Order $order
     * @param string $notificationStatus
     * @return void
     */
    public function sendNotifications(Order $order, string $notificationStatus): void
    {
        if (empty($notificationStatus)) {
            return;
        }

        $payment = $order->getPayment();

        // Only send notification if status has changed
        $lastNotificationStatus = $payment->getAdditionalInformation('koin_last_notification_status') ?? '';
        if ($lastNotificationStatus !== $notificationStatus) {
            $this->notifyOrder($order, $notificationStatus);
        }

        $antifraudNotificationStatus = $payment->getAdditionalInformation('koin_antifraud_notification_status') ?? '';
        if ($antifraudNotificationStatus !== $notificationStatus) {
            $this->notifyAntifraud($order, $notificationStatus);
        }
    }

    /**
     * Send notification based on order state
     *
     * @param Order $order
     * @return void
     */
    public function sendNotificationForOrderState(Order $order): void
    {
        $notificationStatus = $this->getNotificationStatusForState($order);
        $this->sendNotifications($order, $notificationStatus);
    }

    /**
     * Send notification for invoice creation
     *
     * @param Order $order
     * @return void
     */
    public function sendNotificationForInvoice(Order $order): void
    {
        // Check if order is fully invoiced
        if ($order->getTotalInvoiced() >= $order->getGrandTotal() || $order->getTotalDue() == 0) {
            $this->sendNotifications($order, 'COLLECTED');
        }
    }

    /**
     * Send notification for credit memo creation
     *
     * @param Order $order
     * @return void
     */
    public function sendNotificationForCreditmemo(Order $order): void
    {
        // Check if it's a full or partial refund
        if ($order->getTotalRefunded() < $order->getGrandTotal()) {
            $this->sendNotifications($order, 'PARTIALLY_REFUNDED');
        } else {
            $this->sendNotifications($order, 'REFUNDED');
        }
    }

    /**
     * Get notification status for order state
     *
     * @param Order $order
     * @return string
     */
    public function getNotificationStatusForState(Order $order): string
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
                // Check if order is fully paid
                if ($order->getTotalInvoiced() >= $order->getGrandTotal() || $order->getTotalDue() == 0) {
                    return 'COLLECTED';
                }
                return 'AUTHORIZED';
            default:
                return '';
        }
    }

    /**
     * Notify order with status
     *
     * @param Order $order
     * @param string $status
     * @return void
     */
    private function notifyOrder(Order $order, string $status): void
    {
        if ($order->getPayment()->getMethod() == ConfigProvider::CODE) {
            try {
                $this->helperOrder->notification($order, $status);
                $this->updateNotificationStatus($order, 'koin_last_notification_status', $status);
            } catch (\Exception $e) {
                $this->helper->log('Failed to send order notification: ' . $e->getMessage());
            }
        }
    }

    /**
     * Notify antifraud with status
     *
     * @param Order $order
     * @param string $status
     * @return void
     */
    private function notifyAntifraud(Order $order, string $status): void
    {
        if ($this->helper->getAntifraudConfig('active')) {
            try {
                $this->helperAntifraud->notification($order, $status);
                $this->updateNotificationStatus($order, 'koin_antifraud_notification_status', $status);
            } catch (\Exception $e) {
                $this->helper->log('Failed to send antifraud notification: ' . $e->getMessage());
            }
        }
    }

    /**
     * Update notification status in payment additional information
     *
     * @param Order $order
     * @param string $key
     * @param string $value
     * @return void
     */
    private function updateNotificationStatus(Order $order, string $key, string $value): void
    {
        $payment = $order->getPayment();
        $payment->setAdditionalInformation($key, $value);

        try {
            $this->orderRepository->save($order);
        } catch (\Exception $e) {
            $this->helper->log('Failed to save notification status: ' . $e->getMessage());
        }
    }
}
