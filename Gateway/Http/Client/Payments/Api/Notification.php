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

class Notification extends Client
{
    /**
     * @param array $data
     * @param string $orderId
     * @return array
     */
    public function execute($data, $orderId): array
    {
        $path = $this->getEndpointPath('payments/capture', $orderId);
        $method = Request::METHOD_POST;
        return $this->makeRequest($path, $method, $data);
    }


    /**
     * initi payment action on Koin API
     * @param $data
     * @return array
     */
    public function notify($paramId, $data, $queryParams = [], $storeId = null): array
    {
        $path = $this->getEndpointPath('payments/notifications', $paramId);
        $api = $this->getApi($path, 'payments', $storeId);
        if (!empty($queryParams)) {
            $api->setParameterGet($queryParams);
        }
        $api->setMethod(Request::METHOD_PATCH);
        $api->setRawBody($this->json->serialize($data));

        $response = $api->send();
        $content = $response->getBody();
        if ($content && $response->getStatusCode() != 204) {
            $content = $this->json->unserialize($content);
        }

        return [
            'status' => $response->getStatusCode(),
            'response' => $content
        ];
    }
}
