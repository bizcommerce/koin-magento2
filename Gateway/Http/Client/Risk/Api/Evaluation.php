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

namespace Koin\Payment\Gateway\Http\Client\Risk\Api;

use Koin\Payment\Gateway\Http\Client;
use Laminas\Http\Request;

class Evaluation extends Client
{
    /**
     * init payment action on Koin API
     * @param $data
     * @return array
     */
    public function sendData($data): array
    {
        $path = $this->getEndpointPath('risk/evaluations');
        $api = $this->getApi($path, 'antifraud');

        $api->setMethod(Request::METHOD_POST);
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

    /**
     * initi payment action on Koin API
     * @param $data
     * @return array
     */
    public function getStatus($evaluationId): array
    {
        $path = $this->getEndpointPath('risk/get_status', null, $evaluationId);
        $api = $this->getApi($path);

        $api->setMethod(Request::METHOD_GET);

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

    /**
     * initi payment action on Koin API
     * @param $data
     * @return array
     */
    public function cancel($evaluationId): array
    {
        $path = $this->getEndpointPath('risk/cancel', null, $evaluationId);
        $api = $this->getApi($path);

        $api->setMethod(Request::METHOD_DELETE);

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

    /**
     * initi payment action on Koin API
     * @param $data
     * @return array
     */
    public function notification($paramId, $data, $queryParams = [], $storeId = null): array
    {
        $path = $this->getEndpointPath('risk/notifications', null, $paramId);
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
