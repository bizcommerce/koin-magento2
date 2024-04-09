<?php

namespace Koin\Payment\Observer\Sales;

use Koin\Payment\Gateway\Http\Client\Payments\Api;
use Koin\Payment\Helper\Data;
use Koin\Payment\Helper\Order as HelperOrder;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Invoice;

class QuoteSubmitSuccess implements ObserverInterface
{
    /** @var Data  */
    protected $helper;

    /** @var HelperOrder  */
    protected $helperOrder;

    /**
     * @param Data $helper
     * @param HelperOrder $helperOrder
     */
    public function __construct(
        Data $helper,
        HelperOrder $helperOrder
    ) {
        $this->helper = $helper;
        $this->helperOrder = $helperOrder;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        try {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $observer->getEvent()->getOrder();
            $payment = $order->getPayment();
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
            $this->helper->log('CAPTURE ERROR', $e->getMessage());
        }
    }
}
