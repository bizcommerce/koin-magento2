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
 *
 */

namespace Koin\Payment\Gateway\Http\Client\Payments\Api;

use Koin\Payment\Gateway\Http\Client;
use Laminas\Http\Request;

class Tokenize extends Client
{
    protected $apiType = 'tokenize';

    /**
     * @param $data
     * @param int $storeId
     * @return array
     */
    public function execute($data, $storeId = null): array
    {
        $path = $this->getEndpointPath('secure/tokenize');
        $method = Request::METHOD_POST;
        return $this->makeRequest($path, $method, $data, $storeId);
    }
}
