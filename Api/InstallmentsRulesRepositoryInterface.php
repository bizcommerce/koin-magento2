<?php

namespace Koin\Payment\Api;

interface InstallmentsRulesRepositoryInterface
{
    /**
     * Save InstallmentsRules.
     *
     * @param \Koin\Payment\Api\Data\InstallmentsRulesInterface $installmentsRules
     * @return \Koin\Payment\Api\Data\InstallmentsRulesInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Koin\Payment\Api\Data\InstallmentsRulesInterface $installmentsRules
    ): \Koin\Payment\Api\Data\InstallmentsRulesInterface;

    /**
     * Retrieve InstallmentsRules.
     *
     * @param int $installmentsRulesId
     * @return \Koin\Payment\Api\Data\InstallmentsRulesInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById(int $installmentsRulesId): \Koin\Payment\Api\Data\InstallmentsRulesInterface;

    /**
     * Retrieve InstallmentsRules matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Koin\Payment\Api\Data\InstallmentsRulesSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    ): \Koin\Payment\Api\Data\InstallmentsRulesSearchResultsInterface;

    /**
     * Delete InstallmentsRules.
     *
     * @param \Koin\Payment\Api\Data\InstallmentsRulesInterface $installmentsRules
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException|\Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(
        \Koin\Payment\Api\Data\InstallmentsRulesInterface $installmentsRules
    ): bool;

    /**
     * Delete InstallmentsRules by ID.
     *
     * @param int $installmentsRulesId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException|\Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById(int $installmentsRulesId): bool;

}
