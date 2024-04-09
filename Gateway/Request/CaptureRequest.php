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

namespace Koin\Payment\Gateway\Request;

use Koin\Payment\Helper\Data;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Model\Order;

class CaptureRequest implements BuilderInterface
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param array $buildSubject
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function build(array $buildSubject)
    {
        if (!isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        /** @var \Magento\Sales\Model\Order\Payment\Interceptor $payment */
        $payment = $buildSubject['payment']->getPayment();

        /** @var Order $order */
        $order = $payment->getOrder();
        $amountValue = $buildSubject['amount'] ?? $order->getGrandTotal();

        $request = new \stdClass();
        $request->transaction = $this->getTransaction($order);
        $request->amount = $this->getAmount($order, $amountValue);

        $clientConfig = [
            'order_id' => $payment->getAdditionalInformation('order_id'),
            'store_id' => $order->getStoreId()
        ];

        return ['request' => $request, 'client_config' => $clientConfig];
    }

    public function getTransaction($order): \stdClass
    {
        $transaction = new \stdClass();
        $transaction->reference_id = $order->getIncrementId();
        return $transaction;
    }

    /**
     * @param $order
     * @param $amountValue
     * @return \stdClass
     * @throws NoSuchEntityException
     */
    public function getAmount($order, $amountValue): \stdClass
    {
        $amount = new \stdClass();
        $amount->currency_code = $order->getBaseCurrencyCode() ?: $this->helper->getStoreCurrencyCode();
        $amount->value = (float) $amountValue;
        return $amount;
    }
}
