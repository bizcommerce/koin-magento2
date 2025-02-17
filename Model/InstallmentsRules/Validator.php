<?php

namespace Koin\Payment\Model\InstallmentsRules;

use Koin\Payment\Model\ResourceModel\InstallmentsRules\Collection;
use Koin\Payment\Model\ResourceModel\InstallmentsRules\CollectionFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;
use Exception;

/**
 * Class DataProvider
 */
class Validator
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /** @var StoreManagerInterface */
    protected $storeManager;

    /** @var DateTime */
    protected $dateTime;

    /** @var TimezoneInterface */
    protected $timeZone;

    /** @var CustomerSession */
    protected $customerSession;

    public function __construct(
        CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager,
        TimezoneInterface $timeZone,
        DateTime $dateTime,
        CustomerSession $customerSession,
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
        $this->dateTime = $dateTime;
        $this->timeZone = $timeZone;
        $this->customerSession = $customerSession;
    }

    /**
     * @throws NoSuchEntityException
     */
    public function getRules(float $total, string $creditCardNumber = '', int $storeId = 0): Collection
    {
        try {
            if (!$storeId) {
                $storeId = $this->storeManager->getStore()->getId();
            }
            $collection = $this->collectionFactory->create();
            return $this->applyCollectionFilters($collection, $total, $creditCardNumber, $storeId);
        } catch (Exception $e) {
            throw new NoSuchEntityException(__($e->getMessage()));
        }
    }

    protected function applyCollectionFilters(
        Collection $collection,
        float $total,
        string $creditCardNumber,
        int $storeId
    ): Collection {
        $collection->addFieldToFilter('status', 1);
        $collection->addFieldToFilter('store_ids', ['finset' => $storeId]);
        $collection->addFieldToFilter('minimum_amount', [
            ['lteq' => $total],
            ['null' => true]
        ]);

        $collection->addFieldToFilter('maximum_amount', [
            ['gteq' => $total],
            ['null' => true],
            ['eq' => 0]
        ]);

        $date = $this->timeZone->date()->format('Y-m-d');
        $collection->addFieldToFilter('start_date', [
            ['lteq' => $date],
            ['null' => true]
        ]);

        $collection->addFieldToFilter('end_date', [
            ['gteq' => $date],
            ['null' => true]
        ]);

        $customerGroupId = $this->customerSession->getCustomerGroupId();
        $collection->addFieldToFilter('customer_group_ids', [
            ['finset' => $customerGroupId],
            ['null' => true]
        ]);

        $today = $this->timeZone->date()->format('w');
        $collection->addFieldToFilter('days_of_week', [
            ['finset' => $today],
            ['empty' => true],
            ['null' => true]
        ]);

        if ($creditCardNumber) {
            $ccNumbers = preg_replace('/\D/', '', $creditCardNumber);
            $collection->getSelect()->where(
                new \Zend_Db_Expr(
                    "(
                        (payment_methods IS NULL)
                        OR (payment_methods = '')
                        OR ('" . substr($ccNumbers, 0, 6) . "' REGEXP payment_methods)
                        OR ('" . substr($ccNumbers, 0, 8) . "' REGEXP payment_methods)
                        OR ('" . $ccNumbers . "' REGEXP payment_methods)
                    )"
                )
            );

            $collection->getSelect()->where(
                new \Zend_Db_Expr(
                    "(
                        (except_payment_methods IS NULL)
                        OR (except_payment_methods = '')
                        OR ('" . substr($ccNumbers, 0, 6) . "' NOT REGEXP except_payment_methods)
                        OR ('" . substr($ccNumbers, 0, 8) . "' NOT REGEXP except_payment_methods)
                        OR ('" . $ccNumbers . "' NOT REGEXP except_payment_methods)
                    )"
                )
            );

            //Order by priority
            $collection->getSelect()->order('priority DESC');
        }
        return $collection;
    }
}
