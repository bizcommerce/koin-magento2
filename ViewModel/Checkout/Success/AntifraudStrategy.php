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
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Model\Order;
use Koin\Payment\Helper\Data as HelperData;

class AntifraudStrategy implements ArgumentInterface
{
    public function __construct(
        protected Session $checkoutSession,
        protected UrlInterface $urlBuilder,
        protected HelperData $helperData,
        protected ?Order $order = null
    )
    {
    }

    protected function getOrder(): Order
    {
        if (!$this->order) {
            $this->order = $this->checkoutSession->getLastRealOrder();
        }
        return $this->order;
    }

    public function getStrategyLink(): string
    {
        return (string)  $this->getOrder()->getPayment()->getAdditionalInformation('koin_antifraud_strategy_link');
    }

    public function getAntifraudStrategyUrl(): string
    {
        return $this->urlBuilder->getUrl('koin/payment/antifraudstrategy', ['oId' => $this->getOrder()->getId()]);
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

    public function isStrategiesEnabled(): bool
    {
        return (bool) $this->helperData->getAntifraudConfig('active_strategy');
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
