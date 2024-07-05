<?php

namespace Koin\Payment\Observer\Sales;

use Koin\Payment\Gateway\Http\Client\Payments\Api;
use Koin\Payment\Gateway\Http\Client\Payments\Refund;
use Koin\Payment\Helper\Data;
use Koin\Payment\Helper\Order as HelperOrder;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Invoice;

class QuoteSubmitSuccess implements ObserverInterface
{
    /** @var Api */
    private $api;

    /** @var Data  */
    protected $helper;

    /** @var HelperOrder  */
    protected $helperOrder;

    /**
     * @param Api $api
     * @param Data $helper
     * @param HelperOrder $helperOrder
     */
    public function __construct(
        Api $api,
        Data $helper,
        HelperOrder $helperOrder
    ) {
        $this->api = $api;
        $this->helper = $helper;
        $this->helperOrder = $helperOrder;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();
        $payment = $order->getPayment();
        try {
            $apiStatus = $payment->getAdditionalInformation('status');
            if ($payment->getMethod() == \Koin\Payment\Model\Ui\CreditCard\ConfigProvider::CODE) {
                if (
                    $payment->getMethodInstance()->getConfigData('auto_capture')
                    && $apiStatus == Api::STATUS_AUTHORIZED
                ) {
                    $this->helperOrder->captureOrder($order, Invoice::CAPTURE_ONLINE);
                } elseif ($apiStatus == Api::STATUS_COLLECTED) {
                    $this->helperOrder->captureOrder($order, Invoice::CAPTURE_OFFLINE);
                }
            }
        } catch (\Exception $e) {
            if ($payment->getMethodInstance()->getConfigData('void_on_capture_error')) {
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
            $this->helper->log('CAPTURE ERROR', $e->getMessage());
        }
    }
}
