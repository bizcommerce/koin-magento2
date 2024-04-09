<?php

namespace Koin\Payment\Plugin\Order;

use Koin\Payment\Gateway\Http\Client\Payments\Api;
use Koin\Payment\Gateway\Http\Client\Payments\Refund;
use Koin\Payment\Helper\Data;
use Koin\Payment\Helper\Order as HelperOrder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class Creditmemo
{
    /** @var \Koin\Payment\Helper\Order  */
    protected $helperOrder;

    /** @var Data */
    protected $helper;

    /** @var SessionManagerInterface */
    protected $session;

    /** @var Api */
    protected $api;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var CreditmemoRepositoryInterface
     */
    protected $creditmemoRepository;

    protected $allowedMethods = [];

    public function __construct(
        HelperOrder $helperOrder,
        Data $helper,
        SessionManagerInterface $session,
        Api $api,
        OrderRepositoryInterface $orderRepository,
        CreditmemoRepositoryInterface $creditmemoRepository
    ) {
        $this->helperOrder = $helperOrder;
        $this->helper = $helper;
        $this->session = $session;
        $this->api = $api;
        $this->orderRepository = $orderRepository;
        $this->creditmemoRepository = $creditmemoRepository;

        $this->allowedMethods = [
            'koin_pix',
            'koin_cc'
        ];
    }

    public function getAllowedMethods(): array
    {
        return $this->allowedMethods;
    }

    /**
     * @param CreditmemoRepositoryInterface $subject
     * @param $creditmemo
     * @return void
     * @throws \Exception
     */
    public function afterSave(
        CreditmemoRepositoryInterface $subject,
        $creditmemo
    ) {
        return $this->refund($creditmemo);
    }

    protected function refund($creditmemo)
    {
        if (
            $this->helper->getGeneralConfig('refund_offline_creditmemo')
            && !$creditmemo->getInvoiceId()
            && !$creditmemo->getInvoice()
        ) {
            $order = $creditmemo->getOrder();

            /** @var \Magento\Sales\Model\Order\Payment $payment */
            $payment = $order->getPayment();

            if (in_array($payment->getMethod(), $this->getAllowedMethods())) {
                $grandTotal = ($creditmemo->getGrandTotal() > 0)
                    ? $creditmemo->getGrandTotal()
                    : $order->getBaseGrandTotal();
                $alreadyRefunded = (float) $payment->getAdditionalInformation('total_refunded');

                if (
                    !$payment->getAdditionalInformation('refunded')
                    || $alreadyRefunded < $order->getBaseGrandTotal()
                ) {
                    $payload = $this->getRefundRequest($order, $grandTotal);
                    $koinOrderId = $payment->getAdditionalInformation('order_id');
                    $status = $payment->getAdditionalInformation('status');

                    if ($status === 'Collected' || $status === 'Authorized') {
                        $this->api->logRequest($payload, Refund::LOG_NAME);
                        $response = $this->api->refund()->execute($payload, $koinOrderId, $status, $order->getStoreId());
                        $this->api->logResponse($response, Refund::LOG_NAME);
                        $this->api->saveRequest(
                            $payload,
                            $response['response'],
                            $response['status'] ?? null,
                            Refund::LOG_NAME
                        );

                        if ($response['status'] !== 200 || !isset($response['response']['refund_id'])) {
                            throw new LocalizedException(__('Error trying to refund order on Koin'));
                        }

                        if (
                            isset($response['response']['status']['type'])
                            && $response['response']['status']['type'] === Api::STATUS_FAILED
                        ) {
                            throw new LocalizedException(__('Error trying to refund order on Koin'));
                        }

                        $payment = $this->helperOrder->updateRefundedAdditionalInformation($payment, $response['response']);
                        $amount = $response['response']['amount']['value'];
                        $order->addCommentToStatusHistory(
                            __('The order had the amount refunded on Koin. Amount of %1', $amount)
                        );
                        $this->helperOrder->savePayment($payment);
                        $this->orderRepository->save($order);
                    }
                }
            }
        }

        return $creditmemo;
    }

    /**
     * @param $order
     * @param float $amountValue
     * @return \stdClass
     */
    protected function getRefundRequest($order, float $amountValue): \stdClass
    {
        $request = new \stdClass();
        $request->transaction = new \stdClass();
        $request->transaction->reference_id = $order->getIncrementId();
        $request->amount = new \stdClass();
        $request->amount->currency_code = $order->getBaseCurrencyCode() ?: $this->helper->getStoreCurrencyCode();
        $request->amount->value = $amountValue;
        return $request;
    }
}
