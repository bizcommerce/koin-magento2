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

use Koin\Payment\Gateway\Http\Client\Payments\Api;
use Koin\Payment\Model\Ui\CreditCard\ConfigProvider as CcConfigProvider;
use Koin\Payment\Model\Ui\Pix\ConfigProvider as PixConfigProvider;
use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Model\Order;

class Additional implements ArgumentInterface
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

    public function getStatusUrl(): string
    {
        return $this->urlBuilder->getUrl('koin/payment/status', ['oId' => $this->getOrder()->getId()]);
    }

    public function getOrderId(): string
    {
        return $this->getOrder()->getIncrementId();
    }

    public function isPending(): bool
    {
        $method = $this->getOrder()->getPayment()->getMethod();
        if ($method !== CcConfigProvider::CODE && $method !== PixConfigProvider::CODE) {
            return false;
        }

        return in_array(
            $this->getOrder()->getPayment()->getAdditionalInformation('status'),
            $this->getPendingStatuses()
        );
    }

    public function getPendingStatuses(): array
    {
        return [
            Api::STATUS_PUBLISHED,
            Api::STATUS_PENDING,
            Api::STATUS_OPENED,
        ];
    }
}
