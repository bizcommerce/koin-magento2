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
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Payment as PaymentResourceModel;

class NotificationService
{
    /** @var Data  */
    private $helper;

    /** @var Antifraud  */
    private $helperAntifraud;

    /** @var HelperOrder  */
    private $helperOrder;

    /** @var PaymentResourceModel */
    private $paymentResourceModel;

    /** @var Json */
    private $json;

    /**
     * @param Data $helper
     * @param Antifraud $helperAntifraud
     * @param HelperOrder $helperOrder
     * @param PaymentResourceModel $paymentResourceModel
     * @param Json $json
     */
    public function __construct(
        Data $helper,
        Antifraud $helperAntifraud,
        HelperOrder $helperOrder,
        PaymentResourceModel $paymentResourceModel,
        Json $json
    ) {
        $this->helper = $helper;
        $this->helperAntifraud = $helperAntifraud;
        $this->helperOrder = $helperOrder;
        $this->paymentResourceModel = $paymentResourceModel;
        $this->json = $json;
    }

    /**
     * Send notifications if status has changed
     *
     * @param Order $order
     * @param string $notificationStatus
     * @param bool $useDirectUpdate
     * @return void
     */
    public function sendNotifications(Order $order, string $notificationStatus, bool $useDirectUpdate = false): void
    {
        if (empty($notificationStatus)) {
            return;
        }

        $payment = $order->getPayment();
        $wasNotified = false;

        $lastNotificationStatus = $payment->getAdditionalInformation('koin_last_notification_status') ?? '';
        if ($lastNotificationStatus !== $notificationStatus) {
            $payment->setAdditionalInformation('koin_last_notification_status', $notificationStatus);
            $this->notifyOrder($order, $notificationStatus);
            $wasNotified = true;
        }

        $antifraudNotificationStatus = $payment->getAdditionalInformation('koin_antifraud_notification_status') ?? '';
        if ($antifraudNotificationStatus !== $notificationStatus) {
            $payment->setAdditionalInformation('koin_antifraud_notification_status', $notificationStatus);
            $this->notifyAntifraud($order, $notificationStatus);
            $wasNotified = true;
        }

        if ($wasNotified) {
            try {
                if ($useDirectUpdate) {
                    $this->savePaymentAdditionalInfoDirectly($payment);
                } else {
                    $this->helperOrder->savePayment($payment);
                }
            } catch (\Exception $e) {
                $this->helper->log('Failed to save notification status: ' . $e->getMessage());
            }
        }
    }

    /**
     * Send notification based on order state
     *
     * @param Order $order
     * @param bool $useDirectUpdate
     * @return void
     */
    public function sendNotificationForOrderState(Order $order, bool $useDirectUpdate = false): void
    {
        $notificationStatus = $this->getNotificationStatusForState($order);
        $this->sendNotifications($order, $notificationStatus, $useDirectUpdate);
    }

    /**
     * Send notification for invoice creation
     *
     * @param Order $order
     * @return void
     */
    public function sendNotificationForInvoice(Order $order): void
    {
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
                if ($order->getTotalInvoiced() >= $order->getGrandTotal() || $order->getTotalDue() == 0) {
                    return 'COLLECTED';
                }
                return 'AUTHORIZED';
            default:
                return '';
        }
    }

    /**
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @return void
     * @throws LocalizedException
     */
    private function savePaymentAdditionalInfoDirectly($payment): void
    {
        if (!$payment->getId()) {
            return;
        }

        $additionalInfo = $payment->getData('additional_information');
        if (is_array($additionalInfo)) {
            $additionalInfo = $this->json->serialize($additionalInfo);
        }

        $connection = $this->paymentResourceModel->getConnection();
        $connection->update(
            $this->paymentResourceModel->getMainTable(),
            ['additional_information' => $additionalInfo],
            ['entity_id = ?' => (int)$payment->getId()]
        );
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
            } catch (\Exception $e) {
                $this->helper->log('Failed to send antifraud notification: ' . $e->getMessage());
            }
        }
    }
}
