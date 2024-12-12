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

namespace Koin\Payment\ViewModel\Checkout\Success;

use Magento\Checkout\Model\Session;
use Magento\Sales\Model\Order;

class Modal
{
    /**
     * @var Order
     */
    protected $order;

    protected $urlBuilder;

    /**
     * @var Session
     */
    protected $checkoutSession;

    public function __construct(
        Session $checkoutSession,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->urlBuilder = $urlBuilder;

    }

    protected function getOrder(): Order
    {
        if (!$this->order) {
            $this->order = $this->checkoutSession->getLastRealOrder();
        }
        return $this->order;
    }

    public function getChallengeUrl(): string
    {
        return (string) $this->getOrder()->getPayment()->getAdditionalInformation('3ds_return_url');
    }

    public function getOrderUrl(): string
    {
        return $this->urlBuilder->getUrl('sales/order/view', ['order_id' => $this->getOrder()->getId()]);
    }

    public function getOrderId(): string
    {
        return $this->getOrder()->getIncrementId();
    }
}
