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

namespace Koin\Payment\Model\Total\Invoice;

use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;

class Interest extends AbstractTotal
{
    /**
     * @param Invoice $invoice
     *
     * @return $this
     */
    public function collect(Invoice $invoice)
    {
        $order = $invoice->getOrder();
        $invoice->setKoinInterestAmount(0);
        $invoice->setBaseKoinInterestAmount(0);

        if (!$order->hasInvoices()) {
            $amount = $order->getKoinInterestAmount();
            $baseAmount = $order->getBaseKoinInterestAmount();
            if ($amount) {
                $invoice->setKoinInterestAmount($amount);
                $invoice->setBaseKoinInterestAmount($baseAmount);
                $invoice->setGrandTotal($invoice->getGrandTotal() + $amount);
                $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseAmount);
            }
        }

        return $this;
    }
}
