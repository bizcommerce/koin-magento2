<?php

namespace Koin\Payment\Plugin\Adminhtml\Block;

use Koin\Payment\Gateway\Http\Client\Payments\Api;
use Koin\Payment\Model\Ui\CreditCard\ConfigProvider;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Block\Adminhtml\Order\View;

class FetchInfo
{
    protected $authorization;

    protected $url;

    protected $orderRepository;

    public function __construct(
        UrlInterface $url,
        OrderRepositoryInterface $orderRepository,
        AuthorizationInterface $authorization
    ) {
        $this->url = $url;
        $this->orderRepository = $orderRepository;
        $this->authorization = $authorization;
    }

    public function beforeSetLayout(View $subject): void
    {
        if ($this->isAllowedAction('Magento_Sales::review_payment')) {
            $this->addFetchInfoButton($subject);
        }
    }

    protected function addFetchInfoButton(View $subject): void
    {
        $orderId = (int) $subject->getOrderId();
        $order = $this->getOrder($orderId);
        $payment = $order->getPayment();

        if (
            $payment->getMethod() == ConfigProvider::CODE
            && (
                $payment->getAdditionalInformation('status') == Api::STATUS_OPENED
                || $payment->getAdditionalInformation('status') == Api::STATUS_AUTHORIZED
            )
        ) {
            $link = $this->url->getUrl(
                'koin/order/fetch',
                ['order_id' => $orderId]
            );

            $subject->addButton(
                'koin_fetch_info',
                [
                    'label' => __('Fetch Info Koin'),
                    'onclick' => "setLocation('" . $link . "')",
                    'class' => 'action-default koin fetch-info',
                    'after' => 'send_notification'
                ]
            );
        }
    }

    protected function getOrder(int $orderId): OrderInterface
    {
        return $this->orderRepository->get($orderId);
    }

    protected function isAllowedAction($resourceId): bool
    {
        return $this->authorization->isAllowed($resourceId);
    }

}
