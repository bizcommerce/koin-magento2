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

namespace Koin\Payment\Model\Config\Source\Customer;

use Magento\Customer\Model\Attribute;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory;
use Magento\Eav\Model\Entity\TypeFactory;

class Attributes implements \Magento\Framework\Data\OptionSourceInterface
{
    /** @var
     * CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var TypeFactory
     */
    protected $eavTypeFactory;

    public function __construct(
        CollectionFactory $collectionFactory,
        TypeFactory $eavTypeFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->eavTypeFactory = $eavTypeFactory;
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
        /** @var \Magento\Eav\Model\Entity\Type $entityType */
        $entityType = $this->eavTypeFactory->create()->loadByCode('customer');

        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('entity_type_id', $entityType->getId());
        $collection->addFieldToFilter('backend_type', ['nin' => ['decimal', 'boolean']]);
        $collection->addOrder('attribute_code', 'asc');

        $options = ['' => __('-- Empty --')];
        foreach ($collection->getItems() as $attribute) {
            /** @var Attribute $attribute */
            $options[$attribute->getAttributeCode()] = __($attribute->getFrontend()->getLabel());
        }

        return $options;
    }
}
