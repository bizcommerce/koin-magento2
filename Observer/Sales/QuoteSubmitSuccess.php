<?php

namespace Koin\Payment\Observer\Sales;

use Koin\Payment\Gateway\Http\Client\Payments\Api;
use Koin\Payment\Gateway\Http\Client\Payments\Refund;
use Koin\Payment\Helper\Antifraud;
use Koin\Payment\Helper\Data;
use Koin\Payment\Helper\Order as HelperOrder;
use Koin\Payment\Model\Ui\CreditCard\ConfigProvider as CcConfigProvider;
use Koin\Payment\Service\NotificationService;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;

class QuoteSubmitSuccess implements ObserverInterface
{
    public function __construct(
        private Api $api,
        private Data $helper,
        private HelperOrder $helperOrder,
        private Antifraud $helperAntifraud,
        private NotificationService $notificationService
    ) {
    }

    public function execute(Observer $observer): void
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();

        $this->processCapture($order);
        $this->processAntifraud($order);
        $this->processNotifications($order);
    }

    private function processCapture(Order $order): void
    {
        $payment = $order->getPayment();

        if ($payment->getMethod() !== CcConfigProvider::CODE) {
            return;
        }

        try {
            $apiStatus = $payment->getAdditionalInformation('status');

            if ($payment->getMethodInstance()->getConfigData('auto_capture') && $apiStatus == Api::STATUS_AUTHORIZED) {
                $this->helperOrder->captureOrder($order, Invoice::CAPTURE_ONLINE);
            } elseif ($apiStatus == Api::STATUS_COLLECTED) {
                $this->helperOrder->captureOrder($order, Invoice::CAPTURE_OFFLINE);
            }
        } catch (\Exception $e) {
            $this->handleCaptureError($order, $payment, $e);
        }
    }

    private function handleCaptureError(Order $order, $payment, \Exception $e): void
    {
        if (!$payment->getMethodInstance()->getConfigData('void_on_capture_error')) {
            $this->helper->log('CAPTURE ERROR: ' . $e->getMessage());
            return;
        }

        $payload = $this->helperOrder->getRefundRequest($order, $order->getBaseGrandTotal());
        $this->api->logRequest($payload, Refund::LOG_NAME);

        $transaction = $this->api->refund()->execute(
            $payload,
            $payment->getAdditionalInformation('order_id'),
            $payment->getAdditionalInformation('status'),
            $order->getStoreId()
        );

        $this->api->logResponse($transaction, Refund::LOG_NAME);
        $this->api->saveRequest(
            $payload,
            $transaction['response'],
            $transaction['status'] ?? null,
            Refund::LOG_NAME
        );

        throw new \Magento\Framework\Exception\LocalizedException(
            __('An error occurred while capturing the payment. The order has been canceled.')
        );
    }

    private function processAntifraud(Order $order): void
    {
        try {
            if ($this->helperAntifraud->isEligibleForAnalysis($order)) {
                $this->helperAntifraud->sendAnalysis($order);
            }
        } catch (\Exception $e) {
            $this->helper->log('ANTIFRAUD ERROR: ' . $e->getMessage());
        }
    }

    private function processNotifications(Order $order): void
    {
        try {
            $this->notificationService->sendNotificationForOrderState($order);
        } catch (\Exception $e) {
            $this->helper->log('NOTIFICATION ERROR: ' . $e->getMessage());
        }
    }
}
