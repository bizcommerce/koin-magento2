<?php

/**
 * @package Koin\Payment
 * @copyright Copyright (c) 2022
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Koin\Payment\Block\Adminhtml\Order\View;

use Koin\Payment\Api\AntifraudRepositoryInterface;
use Koin\Payment\Helper\Data;
use Koin\Payment\Model\Antifraud;
use Koin\Payment\Model\ResourceModel\Antifraud\Collection;
use Koin\Payment\Model\ResourceModel\Antifraud\CollectionFactory;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order;

class FraudAnalysis extends Template
{
    protected $antifraud;
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry = null;

    /** @var CollectionFactory */
    protected $antifraudCollectionFactory;

    /** @var AntifraudRepositoryInterface */
    protected $antifraudRepository;

    /** @var SearchCriteriaBuilder */
    protected $searchCriteriaBuilder;

    /** @var FilterBuilder */
    protected $filterBuilder;

    /** @var Data */
    protected $helper;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param CollectionFactory $callbackFactory
     * @param AntifraudRepositoryInterface $antifraudRepository
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        CollectionFactory $callbackFactory,
        AntifraudRepositoryInterface $antifraudRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        Data $helper,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->antifraudCollectionFactory = $callbackFactory;
        $this->antifraudRepository = $antifraudRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * @return Antifraud|DataObject|null
     */
    public function getAntiFraud()
    {
        if (!$this->antifraud) {
            /** @var Collection $collection */
            $collection = $this->antifraudCollectionFactory->create();
            $collection->addFieldToFilter('increment_id', $this->getOrder()->getIncrementId());
            if ($collection->getSize()) {
                $this->antifraud = $collection->getFirstItem();
            }
        }
        return $this->antifraud;
    }

    /**
     * Retrieve order model instance
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }

    /**
     * @inheirtDoc
     */
    public function toHtml()
    {
        if ($this->getAntiFraud()) {
            return parent::toHtml();
        }

        return '';
    }
}
