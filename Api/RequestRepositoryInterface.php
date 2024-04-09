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

interface RequestRepositoryInterface
{
    /**
     * Save Queue
     * @param \Koin\Payment\Api\Data\RequestInterface $callback
     * @return \Koin\Payment\Api\Data\RequestInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Koin\Payment\Api\Data\RequestInterface $callback
    );

    /**
     * Retrieve RequestInterface
     * @param string $id
     * @return \Koin\Payment\Api\Data\RequestInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($id);

    /**
     * Retrieve Queue matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Koin\Payment\Api\Data\RequestSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );
}
