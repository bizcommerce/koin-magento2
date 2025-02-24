<?php

/**
 * Biz
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Biz.com license that is
 * available through the world-wide-web at this URL:
 * https://www.bizcommerce.com.br/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Biz
 * @package     Koin_Payment
 * @copyright   Copyright (c) Biz (https://www.bizcommerce.com.br/)
 * @license     https://www.bizcommerce.com.br/LICENSE.txt
 */

namespace Koin\Payment\Model\Config\Source\InstallmentRules;

use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Api\Data\AttributeSetSearchResultsInterface;
use Magento\Eav\Model\ResourceModel\Entity\Type;
use Magento\Eav\Model\Entity\TypeFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Data\OptionSourceInterface;

class ProductSets implements OptionSourceInterface
{
    /**
     * @var AttributeSetRepositoryInterface
     */
    private $attributeSetRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var Type
     */
    private $resourceType;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * Constructor
     *
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param Type $resourceType
     * @param TypeFactory $typeFactory
     */
    public function __construct(
        AttributeSetRepositoryInterface $attributeSetRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        Type $resourceType,
        TypeFactory $typeFactory
    ) {
        $this->attributeSetRepository = $attributeSetRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->resourceType = $resourceType;
        $this->typeFactory = $typeFactory;
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            ['value' => '', 'label' => __('...')],
        ];

        $entityTypeId = $this->getEntityTypeId();

        $entityTypeFilter = $this->filterBuilder
            ->setField('entity_type_id')
            ->setValue($entityTypeId)
            ->setConditionType('eq')
            ->create();

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilters([$entityTypeFilter])
            ->create();

        /** @var AttributeSetSearchResultsInterface $attributeSets */
        $attributeSets = $this->attributeSetRepository->getList($searchCriteria)->getItems();

        foreach ($attributeSets as $attributeSet) {
            $options[] = [
                'value' => $attributeSet->getAttributeSetId(),
                'label' => $attributeSet->getAttributeSetName(),
            ];
        }

        return $options;
    }

    protected function getEntityTypeId(): int
    {
        $entityType = $this->typeFactory->create();
        $this->resourceType->loadByCode($entityType, 'catalog_product');
        return $entityType->getId();
    }
}
