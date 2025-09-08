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

namespace Koin\Payment\Gateway\Response\Pix;

use Koin\Payment\Helper\Order as HelperOrder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Api\Data\TransactionInterface;

class TransactionHandler implements HandlerInterface
{
    /**
     * @var \Koin\Payment\Helper\Order
     */
    protected $helperOrder;

    /**
     * constructor.
     * @param HelperOrder $helperOrder
     */
    public function __construct(
        HelperOrder $helperOrder
    ) {
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
            throw new \InvalidArgumentException(__('Payment data object should be provided'));
        }

        /** @var PaymentDataObjectInterface $paymentData */
        $paymentData = $handlingSubject['payment'];
        $transaction = $response['transaction'];
        $additionalData = $response['additional_data'] ?? [];

        if (isset($response['status_code']) && $response['status_code'] >= 300) {
            throw new LocalizedException(__('There was an error processing your request.'));
        }

        /** @var $payment \Magento\Sales\Model\Order\Payment */
        $payment = $paymentData->getPayment();
        $payment = $this->helperOrder->updateDefaultAdditionalInfo($payment, $transaction);
        $payment = $this->helperOrder->updatePixAdditionalInfo($payment, $transaction);
        $payment = $this->helperOrder->updateRequestAdditionalData($payment, $additionalData);
        $payment->setIsTransactionClosed(false);

        $state = $this->helperOrder->getPaymentStatusState($payment);

        if ($this->helperOrder->canSkipOrderProcessing($state)) {
            $payment->getOrder()->setState($state);
            $payment->setSkipOrderProcessing(true);
            $payment->addTransaction(TransactionInterface::TYPE_AUTH);
        }
    }
}
