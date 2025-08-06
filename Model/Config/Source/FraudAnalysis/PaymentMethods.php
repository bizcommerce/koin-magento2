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

namespace Koin\Payment\Model\Config\Source\FraudAnalysis;

use Magento\Customer\Model\Attribute;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory;
use Magento\Eav\Model\Entity\TypeFactory;

class PaymentMethods implements \Magento\Framework\Data\OptionSourceInterface
{
    /** @var
     * CollectionFactory
     */
    protected $paymentMethodList;

    /**
     * @var TypeFactory
     */
    protected $eavTypeFactory;

    public function __construct(
        \Magento\Payment\Model\PaymentMethodList $paymentMethodList
    ) {
        $this->paymentMethodList = $paymentMethodList;
    }

   public function toOptionArray()
    {
        $options = [];
        foreach ($this->getOptions() as $optionValue => $optionLabel) {
            $options[] = ['value' => $optionValue, 'label' => $optionLabel];
        }
        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->getOptions();
    }

    protected function getOptions(): array
    {
        $methodsList = $this->paymentMethodList->getList(0);

        $options = ['' => __('-- Empty --')];
        /** @var \Magento\Payment\Api\Data\PaymentMethodInterface $method */
        foreach ($methodsList as $method) {
            $options[$method->getCode()] = __($method->getTitle()) . ('(' . $method->getCode() . ')');
        }

        return $options;
    }
}
