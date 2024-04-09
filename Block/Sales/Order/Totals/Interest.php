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

namespace Koin\Payment\Block\Sales\Order\Totals;

use Magento\Sales\Model\Order;

/**
 * Class Interest
 *
 * @package MercadoPago\Core\Block\Sales\Order\Totals
 */
class Interest extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_source;

    /**
     * Get data (totals) source model
     *
     * @return \Magento\Framework\DataObject
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * Add this total to parent
     */
    public function initTotals()
    {
        if ($this->getSource()->getKoinInterestAmount() > 0) {
            $total = new \Magento\Framework\DataObject([
                'code'  => 'koin_interest',
                'field' => 'koin_interest_amount',
                'value' => $this->getSource()->getKoinInterestAmount(),
                'label' => __('Interest Rate'),
            ]);

            $this->getParentBlock()->addTotalBefore($total, $this->getBeforeCondition());
        }

        return $this;
    }
}
