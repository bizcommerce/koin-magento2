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

use Koin\Payment\Model\InstallmentsRulesFactory;
use Koin\Payment\Api\Data\InstallmentsRulesInterfaceFactory;
use Koin\Payment\Api\Data\InstallmentsRulesSearchResultsInterfaceFactory;
use Koin\Payment\Api\InstallmentsRulesRepositoryInterface;
use Koin\Payment\Model\ResourceModel\InstallmentsRules as ResourceInstallmentsRules;
use Koin\Payment\Model\ResourceModel\InstallmentsRules\CollectionFactory as InstallmentsRulesCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class InstallmentsRulesRepository implements InstallmentsRulesRepositoryInterface
{
    /** @var ResourceInstallmentsRules  */
    protected $resource;

    /** @var InstallmentsRulesFactory  */
    protected $installmentsRulesFactory;

    /** @var InstallmentsRulesCollectionFactory  */
    protected $installmentsRulesCollectionFactory;

    /** @var InstallmentsRulesSearchResultsInterfaceFactory  */
    protected $searchResultsFactory;

    /** @var InstallmentsRulesInterfaceFactory  */
    protected $dataInstallmentsRulesFactory;

    /** @var CollectionProcessorInterface  */
    private $collectionProcessor;

    /**
     * @param ResourceInstallmentsRules $resource
     * @param InstallmentsRulesFactory $installmentsRulesFactory
     * @param InstallmentsRulesInterfaceFactory $dataInstallmentsRulesFactory
     * @param InstallmentsRulesCollectionFactory $installmentsRulesCollectionFactory
     * @param InstallmentsRulesSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceInstallmentsRules $resource,
        InstallmentsRulesFactory $installmentsRulesFactory,
        InstallmentsRulesInterfaceFactory $dataInstallmentsRulesFactory,
        InstallmentsRulesCollectionFactory $installmentsRulesCollectionFactory,
        InstallmentsRulesSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->installmentsRulesFactory = $installmentsRulesFactory;
        $this->installmentsRulesCollectionFactory = $installmentsRulesCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataInstallmentsRulesFactory = $dataInstallmentsRulesFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Koin\Payment\Api\Data\InstallmentsRulesInterface $installmentsRules
    ): \Koin\Payment\Api\Data\InstallmentsRulesInterface {
        try {
            $this->resource->save($installmentsRules);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the installmentsRules info: %1',
                $exception->getMessage()
            ));
        }
        return $installmentsRules;
    }

    /**
     * {@inheritdoc}
     */
    public function getById(int $installmentsRulesId): \Koin\Payment\Api\Data\InstallmentsRulesInterface
    {
        $installmentsRules = $this->installmentsRulesFactory->create();
        $this->resource->load($installmentsRules, $installmentsRulesId);
        if (!$installmentsRules->getId()) {
            throw new NoSuchEntityException(
                __("The entity that was requested doesn't exist. Verify the entity and try again.")
            );
        }
        return $installmentsRules;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ): \Koin\Payment\Api\Data\InstallmentsRulesSearchResultsInterface {
        $collection = $this->installmentsRulesCollectionFactory->create();

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

    /**
     * {@inheritdoc}
     */
    public function delete(
        \Koin\Payment\Api\Data\InstallmentsRulesInterface $installmentsRules
    ): bool {
        try {
            $this->resource->delete($installmentsRules);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the InstallmentsRules: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($installmentsRulesId): bool
    {
        return $this->delete($this->getById($installmentsRulesId));
    }
}
