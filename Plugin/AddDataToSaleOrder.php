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

namespace Koin\Payment\Plugin;

use Magento\Sales\Api\OrderRepositoryInterface;

class AddDataToSaleOrder
{
    /**
     * @param OrderRepositoryInterface $subject
     * @param $result
     * @return mixed
     */
    public function afterGet(OrderRepositoryInterface $subject, $result)
    {
        $extensionAttributes = $result->getExtensionAttributes();

        $extensionAttributes->setData('koin_antifraud_score', $result->getData('koin_antifraud_score'));
        $extensionAttributes->setData('koin_antifraud_status', $result->getData('koin_antifraud_status'));

        $result->setExtensionAttributes($extensionAttributes);

        return $result;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param $result
     * @return mixed
     */
    public function afterGetList(
        OrderRepositoryInterface $subject,
        $result
    ) {
        foreach ($result->getItems() as $order) {
            $this->afterGet($subject, $order);
        }

        return $result;
    }
}
