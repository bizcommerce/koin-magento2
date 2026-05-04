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

namespace Koin\Payment\Gateway\Response;

use Koin\Payment\Gateway\Http\Client\Payments\Api;
use Koin\Payment\Helper\Data as HelperData;
use Koin\Payment\Helper\Order as HelperOrder;
use Koin\Payment\Model\Ui\CreditCard\ConfigProvider as CcConfigProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Invoice;

class FetchInfoHandler implements HandlerInterface
{
    protected $helperData;

    protected $helperOrder;

    protected $orderRepository;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        HelperData $helperData,
        HelperOrder $helperOrder
    ) {
        $this->orderRepository = $orderRepository;
        $this->helperData = $helperData;
        $this->helperOrder = $helperOrder;
    }

    /**
     * Handles transaction id
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function handle(array $handlingSubject, array $response)
    {
        if (!isset($handlingSubject['payment'])
            || !$handlingSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        /** @var PaymentDataObjectInterface $paymentData */
        $paymentData = $handlingSubject['payment'];
        $transaction = $response['transaction'];

        if (isset($response['status_code']) && $response['status_code'] >= 300) {
            throw new LocalizedException(__('There was an error processing your request.'));
        }

        $responseStatus = $transaction['status'] ?? [];
        if (empty($responseStatus)) {
            throw new LocalizedException(__('There was an error processing your request.'));
        }

        /** @var $payment \Magento\Sales\Model\Order\Payment */
        $payment = $paymentData->getPayment();
        $order = $payment->getOrder();

        if (isset($transaction['status'])) {
            if (isset($transaction['status']['type'])) {
                $koinStatus = $transaction['status']['type'];
                $payment->setAdditionalInformation('status', $transaction['status']['type']);
                if ($koinStatus == Api::STATUS_COLLECTED) {
                    $this->helperOrder->invoiceOrder($order, $order->getBaseGrandTotal());
                    $orderStatus = $this->helperData->getConfig('paid_order_status', $payment->getMethod());
                    $order->setStatus($orderStatus);
                    $order->setState($this->helperOrder->getStatusState($orderStatus));
                } elseif ($koinStatus == Api::STATUS_FAILED) {
                    $order = $this->helperOrder->cancelOrder($order, $order->getBaseGrandTotal(), true);
                } elseif ($koinStatus == Api::STATUS_OPENED) {
                    throw new LocalizedException(__('The transaction is still open.'));
                }
            }
            if (isset($transaction['status']['date'])) {
                $payment->setAdditionalInformation('status_date', $transaction['status']['date']);
            }

            $this->orderRepository->save($order);
            $this->helperOrder->savePayment($payment);
        }
    }
}
