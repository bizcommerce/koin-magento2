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

use Koin\Payment\Model\QueueFactory;
use Koin\Payment\Api\Data\QueueInterfaceFactory;
use Koin\Payment\Api\Data\QueueSearchResultsInterfaceFactory;
use Koin\Payment\Api\QueueRepositoryInterface;
use Koin\Payment\Model\ResourceModel\Queue as ResourceQueue;
use Koin\Payment\Model\ResourceModel\Queue\CollectionFactory as QueueCollectionFactory;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class QueueRepository implements QueueRepositoryInterface
{

    /** @var ResourceQueue  */
    protected $resource;

    /** @var QueueFactory  */
    protected $queueFactory;

    /** @var QueueCollectionFactory  */
    protected $queueCollectionFactory;

    /** @var QueueSearchResultsInterfaceFactory  */
    protected $searchResultsFactory;

    /** @var QueueInterfaceFactory  */
    protected $dataQueueFactory;

    /** @var JoinProcessorInterface  */
    protected $extensionAttributesJoinProcessor;

    /** @var CollectionProcessorInterface  */
    private $collectionProcessor;

    /**
     * @param ResourceQueue $resource
     * @param QueueFactory $queueFactory
     * @param QueueInterfaceFactory $dataQueueFactory
     * @param QueueCollectionFactory $queueCollectionFactory
     * @param QueueSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     */
    public function __construct(
        ResourceQueue $resource,
        QueueFactory $queueFactory,
        QueueInterfaceFactory $dataQueueFactory,
        QueueCollectionFactory $queueCollectionFactory,
        QueueSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor
    ) {
        $this->resource = $resource;
        $this->queueFactory = $queueFactory;
        $this->queueCollectionFactory = $queueCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataQueueFactory = $dataQueueFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Koin\Payment\Api\Data\QueueInterface $queue
    ) {
        try {
            $queue = $this->resource->save($queue);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the queue info: %1',
                $exception->getMessage()
            ));
        }
        return $queue;
    }

    /**
     * {@inheritdoc}
     */
    public function get($queueId)
    {
        $queue = $this->queueFactory->create();
        $this->resource->load($queue, $queueId);
        if (!$queue->getId()) {
            throw new NoSuchEntityException(
                __("The entity that was requested doesn't exist. Verify the entity and try again.")
            );
        }
        return $queue;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->queueCollectionFactory->create();

        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Koin\Payment\Api\Data\QueueInterface::class
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
