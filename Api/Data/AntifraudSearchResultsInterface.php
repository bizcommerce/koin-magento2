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

declare(strict_types=1);

namespace Koin\Payment\Api\Data;

interface AntifraudSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get transaction list.
     * @return \Koin\Payment\Api\Data\AntifraudInterface[]
     */
    public function getItems();

    /**
     * Set entity_id list.
     * @param \Koin\Payment\Api\Data\AntifraudInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

