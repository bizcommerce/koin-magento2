<?php
/**
 *
 *
 *
 *
 *
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Koin
 * @package     Koin_Payment
 *
 *
 */

namespace Koin\Payment\Observer\Sales;

use Koin\Payment\Helper\Data;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\DataObject\Copy;
use Magento\Framework\Event\ObserverInterface;

class QuoteSubmitBefore implements ObserverInterface
{
    /** @var Copy */
    protected $objectCopyService;

    /** @var CustomerSession  */
    protected $customerSession;

    /** @var Data  */
    protected $helper;

    /**
     * @param Copy $objectCopyService
     * ...
     */
    public function __construct(
        Data $helper,
        Copy $objectCopyService,
        CustomerSession $customerSession
    ) {
        $this->objectCopyService = $objectCopyService;
        $this->helper = $helper;
        $this->customerSession = $customerSession;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return QuoteSubmitBefore
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getData('order');
        /* @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getData('quote');

        $this->objectCopyService->copyFieldsetToTarget('sales_convert_quote', 'to_order', $quote, $order);

        try {
            if ($this->helper->getAntifraudConfig('active')) {
                /** @var \Magento\Sales\Model\Order $order */
                $order->setData('koin_antifraud_fingerprint', $this->customerSession->getSessionId());
            }
        } catch (\Exception $e) {
            $this->helper->log($e->getMessage());
        }

        return $this;
    }
}

