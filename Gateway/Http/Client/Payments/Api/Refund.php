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
use Laminas\Http\Request;

class Refund extends Client
{
    public const CANCEL_STATUS = ['Opened', 'Authorized', 'Published'];

    /**
     * @param array $data
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
        $path = $this->getEndpointPath('payments/refund', $orderId);
        $method = Request::METHOD_PUT;
        return $this->makeRequest($path, $method, $data, $storeId);
    }
}
