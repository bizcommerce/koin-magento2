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

namespace Koin\Payment\Model\ResourceModel;

use Koin\Payment\Model\AntifraudFactory;
use Koin\Payment\Api\Data\AntifraudInterfaceFactory;
use Koin\Payment\Api\Data\AntifraudSearchResultsInterfaceFactory;
use Koin\Payment\Api\AntifraudRepositoryInterface;
use Koin\Payment\Model\ResourceModel\Antifraud as ResourceAntifraud;
use Koin\Payment\Model\ResourceModel\Antifraud\CollectionFactory as AntifraudCollectionFactory;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class AntifraudRepository implements AntifraudRepositoryInterface
{

    /** @var ResourceAntifraud  */
    protected $resource;

    /** @var AntifraudFactory  */
    protected $antifraudFactory;

    /** @var AntifraudCollectionFactory  */
    protected $antifraudCollectionFactory;

    /** @var AntifraudSearchResultsInterfaceFactory  */
    protected $searchResultsFactory;

    /** @var AntifraudInterfaceFactory  */
    protected $dataAntifraudFactory;

    /** @var JoinProcessorInterface  */
    protected $extensionAttributesJoinProcessor;

    /** @var CollectionProcessorInterface  */
    private $collectionProcessor;

    /**
     * @param ResourceAntifraud $resource
     * @param AntifraudFactory $antifraudFactory
     * @param AntifraudInterfaceFactory $dataAntifraudFactory
     * @param AntifraudCollectionFactory $antifraudCollectionFactory
     * @param AntifraudSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     */
    public function __construct(
        ResourceAntifraud $resource,
        AntifraudFactory $antifraudFactory,
        AntifraudInterfaceFactory $dataAntifraudFactory,
        AntifraudCollectionFactory $antifraudCollectionFactory,
        AntifraudSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor
    ) {
        $this->resource = $resource;
        $this->antifraudFactory = $antifraudFactory;
        $this->antifraudCollectionFactory = $antifraudCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataAntifraudFactory = $dataAntifraudFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        /** @var \Koin\Payment\Model\Antifraud $antifraud */
        $antifraud = $this->antifraudFactory->create();
        $this->resource->load($antifraud, $id);
        if (!$antifraud->getId()) {
            throw new NoSuchEntityException(__('Item with id "%1" does not exist.', $id));
        }

        return $antifraud;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Koin\Payment\Api\Data\AntifraudInterface $antifraud
    ) {
        try {
            $this->resource->save($antifraud);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the antifraud info: %1',
                $exception->getMessage()
            ));
        }
        return $antifraud;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->antifraudCollectionFactory->create();
        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Koin\Payment\Api\Data\AntifraudInterface::class
        );

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $items = [];
        foreach ($collection as $model) {
            $items[] = $model;
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }
}
