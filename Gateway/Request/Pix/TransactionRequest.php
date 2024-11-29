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

namespace Koin\Payment\Gateway\Request\Pix;

use Koin\Payment\Gateway\Request\PaymentsRequest;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;

class TransactionRequest extends PaymentsRequest implements BuilderInterface
{
    /**
     * Builds ENV request
     *
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
        $order = $payment->getOrder();

        $request = new \stdClass();
        $request->store = $this->getStoreData();
        $request->transaction = $this->getTransaction($order, $buildSubject['amount']);
        $request->payment_method = $this->getPaymentMethod($order, $payment->getMethod());
        $request->payer = $this->getPayerData($order);
        $request->country_code = $this->helper->getDefaultCountryCode();
        $request->descriptor = __('Order %1 on %2', $order->getRealOrderId(), $this->helper->getStoreName());
        $request->notification_url = [$this->helper->getPaymentsNotificationUrl($order)];

        return [
            'request' => $request,
            'client_config' => [
                'store_id' => $order->getStoreId(),
                'additional_data' => [
                    'expiration_date' => $this->getExpirationDate('koin_pix')
                ]
            ]
        ];
    }

    /**
     * @param $paymentMethod
     * @return \stdClass
     */
    protected function getPaymentMethod($order, $paymentMethodCode): \stdClass
    {
        $payment = parent::getPaymentMethod($order, $paymentMethodCode);
        $payment->expiration_date = $this->getExpirationDate('koin_pix');
        if ($this->helper->getConfig('use_custom_pix_key', 'koin_pix')) {
            $customPixKey = trim($this->helper->getConfig('custom_pix_key', 'koin_pix'));
            if ($customPixKey) {
                $payment->payment_key = $customPixKey;
            }
        }
        return $payment;
    }
}
