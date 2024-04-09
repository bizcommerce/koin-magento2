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

namespace Koin\Payment\Plugin\Order;

use Magento\Sales\Model\Order\Payment as OrderPayment;

class Payment
{
    /**
     * @param OrderPayment $subject
     * @param $result
     * @return bool
     */
    public function afterCanVoid(OrderPayment $subject, $result)
    {
        if (!$result) {
            return (bool) $subject->getMethodInstance()->canVoid();
        }
        return $result;
    }
}
