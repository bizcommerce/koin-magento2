<?php
/**
 * Biz
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Biz.com license that is
 * available through the world-wide-web at this URL:
 * https://www.bizcommerce.com.br/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Biz
 * @package     Koin_Payment
 * @copyright   Copyright (c) Biz (https://www.bizcommerce.com.br/)
 * @license     https://www.bizcommerce.com.br/LICENSE.txt
 */

namespace Koin\Payment\Observer\Sales;

use Koin\Payment\Helper\Data;
use Koin\Payment\Helper\Antifraud;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class OrderCancelAfter implements ObserverInterface
{
    /** @var Data  */
    protected $helper;

    /** @var Data  */
    protected $helperAntifraud;

    /**
     * @param Data $helper
     * @param Antifraud $helperAntifraud
     */
    public function __construct(
        Data $helper,
        Antifraud $helperAntifraud
    ) {
        $this->helper = $helper;
        $this->helperAntifraud = $helperAntifraud;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return OrderCancelAfter
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            if ($this->helper->getAntifraudConfig('active')) {
                /* @var \Magento\Sales\Model\Order $order */
                $order = $observer->getEvent()->getData('order');
                $this->helperAntifraud->removeAntifraud($order);
            }

        } catch (\Exception $e) {
            $this->helper->log($e->getMessage());
        }

        return $this;
    }
}

