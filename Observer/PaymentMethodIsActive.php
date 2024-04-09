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

namespace Koin\Payment\Observer;

use Koin\Payment\Model\Ui\Redirect\ConfigProvider;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Koin\Payment\Helper\Data as HelperData;

class PaymentMethodIsActive implements ObserverInterface
{
    protected $helper;

    public function __construct(
        HelperData $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        $methodCode = $event->getMethodInstance()->getCode();

        if (
            $methodCode == ConfigProvider::CODE
            && $this->helper->isCompanyCustomer()
        ) {
            /** @var DataObject $result */
            $result = $observer->getEvent()->getResult();
            $result->setData('is_available', false);
        }
    }
}
