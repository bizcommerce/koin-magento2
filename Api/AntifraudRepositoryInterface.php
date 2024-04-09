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
 * @author      Koin
 */

declare(strict_types=1);

namespace Koin\Payment\Api;

interface AntifraudRepositoryInterface
{
    /**
     * Save Queue
     * @param \Koin\Payment\Api\Data\AntifraudInterface $callback
     * @return \Koin\Payment\Api\Data\AntifraudInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Koin\Payment\Api\Data\AntifraudInterface $callback
    );

    /**
     * Retrieve Antifraud
     * @param string $id
     * @return \Koin\Payment\Api\Data\AntifraudInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($id);

    /**
     * Retrieve Queue matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Koin\Payment\Api\Data\AntifraudSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );
}
