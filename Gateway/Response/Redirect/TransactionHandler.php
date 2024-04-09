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

namespace Koin\Payment\Gateway\Response\Redirect;

use Koin\Payment\Gateway\Http\Client\Payments\Api;
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
     * {
    "status": {
    "type": "Opened",
    "date": "2023-05-11T13:53:38.024Z"
    },
    "store": {
    "code": "GATEWAY"
    },
    "transaction": {
    "reference_id": "000000038",
    "business_id": "000000038",
    "account": "066",
    "amount": {
    "currency_code": "BRL",
    "value": 105
    }
    },
    "country_code": "BR",
    "return_url": "https://payments.koin.com.br/checkout/fcf34d7a-cb24-4e08-ac04-6fcba73c04e5"
    }
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

        if (isset($response['status_code']) && $response['status_code'] >= 300) {
            throw new LocalizedException(__('There was an error processing your request.'));
        }

        $responseStatus = $transaction['status'] ?? [];
        $responseStatusType = $responseStatus['type'] ?? null;
        if (empty($responseStatus) || $responseStatusType === Api::STATUS_FAILED) {
            throw new LocalizedException(__('There was an error processing your request.'));
        }

        /** @var $payment \Magento\Sales\Model\Order\Payment */
        $payment = $paymentData->getPayment();
        $payment = $this->helperOrder->updateDefaultAdditionalInfo($payment, $transaction);
        $payment = $this->helperOrder->updateRedirectAdditionalInfo($payment, $transaction);

        $payment->setIsTransactionClosed(false);
        $state = $this->helperOrder->getPaymentStatusState($payment);

        if ($this->helperOrder->canSkipOrderProcessing($state)) {
            $payment->getOrder()->setState($state);
            $payment->setSkipOrderProcessing(true);
            $payment->addTransaction(TransactionInterface::TYPE_ORDER);
        }
    }
}
