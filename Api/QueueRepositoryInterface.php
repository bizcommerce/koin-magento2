<?php

/**
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Koin
 * @package     Koin_Payment
 *
 */

declare(strict_types=1);

namespace Koin\Payment\Api;

interface QueueRepositoryInterface
{
    /**
     * Save Queue
     * @param \Koin\Payment\Api\Data\QueueInterface $queue
     * @return \Koin\Payment\Api\Data\QueueInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Koin\Payment\Api\Data\QueueInterface $queue
    );

    /**
     * Retrieve Queue matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Koin\Payment\Api\Data\QueueSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Retrieve Queue
     * @param string $queueId
     * @return \Koin\Payment\Api\Data\QueueInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($queueId);
}
