<?php

/**
 *
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

class Query extends Client
{
    public function execute(string $orderId, $storeId = null): array
    {
        $path = $this->getEndpointPath('payments/get', $orderId);
        $method = Request::METHOD_GET;
        return $this->makeRequest($path, $method, [], $storeId);
    }
}
