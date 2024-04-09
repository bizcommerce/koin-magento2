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

namespace Koin\Payment\Model\Total\Creditmemo;

use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;

class Interest extends AbstractTotal
{
    /**
     * @param Creditmemo $creditmemo
     * @return $this
     */
    public function collect(Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();
        $creditmemo->setKoinInterestAmount(0);
        $creditmemo->setBaseKoinInterestAmount(0);

        if (!$order->hasCreditmemos()) {
            $amount = $order->getKoinInterestAmount();
            $baseAmount = $order->getBaseKoinInterestAmount();
            if ($amount) {
                $creditmemo->setKoinInterestAmount($amount);
                $creditmemo->setBaseKoinInterestAmount($baseAmount);
                $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $amount);
                $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseAmount);
            }
        }

        return $this;
    }
}
