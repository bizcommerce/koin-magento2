<?php

/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Koin
 * @package     Koin_Payment
 *
 */

namespace Koin\Payment\Gateway\Http\Client\Payments\Api;

use Koin\Payment\Gateway\Http\Client;
use Koin\Payment\Gateway\Http\Client\Payments\Api;
use Laminas\Http\Request;

class Refund extends Client
{
    public const CANCEL_STATUS = ['Opened', 'Authorized', 'Published'];

    /**
     * @param array|\stdClass $data
     * @param string $orderId
     * @param string $status
     * @param int $storeId
     * @return array
     */
    public function execute($data, $orderId, $status, $storeId = null): array
    {
        if (in_array($status, self::CANCEL_STATUS)) {
            return $this->cancel([], $orderId, $storeId);
        }
        return $this->refund($data, $orderId, $storeId);
    }

    public function cancel($data, $orderId, $storeId): array
    {
        $path = $this->getEndpointPath('payments/cancel', $orderId);
        $method = Request::METHOD_PUT;
        return $this->makeRequest($path, $method, $data, $storeId);
    }

    public function refund($data, $orderId, $storeId): array
    {
        if (
            is_object($data) && $data->amount->value == 0
            || is_array($data) && $data['amount']['value'] == 0
        ) {
            return $this->refundEmtpyValue();
        }

        $path = $this->getEndpointPath('payments/refund', $orderId);
        $method = Request::METHOD_PUT;
        return $this->makeRequest($path, $method, $data, $storeId);
    }

    protected function refundEmtpyValue(): array
    {
        return [
            'status' => 200,
            'response' => [
                'refund_id' => '',
                'message' => 'refund not allowed without amount value',
                'amount' => [
                    'value' => 0
                ],
                'status' => [
                    'type' => Api::STATUS_UNKNOWN
                ]
            ]
        ];
    }
}
