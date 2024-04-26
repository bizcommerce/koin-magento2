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

namespace Koin\Payment\Gateway\Http\Client\Payments;

use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Koin\Payment\Helper\Data;

class Refund implements ClientInterface
{
    const LOG_NAME = 'koin-refund';

    /**
     * @var Api
     */
    private $api;

    /**
     * @param Data $helper
     * @param Api $api
     */
    public function __construct(
        Api $api
    ) {
        $this->api = $api;
    }

    /**
     * Places request to gateway. Returns result as ENV array
     *
     * @param TransferInterface $transferObject
     * @return array
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $requestBody = $transferObject->getBody();
        $config = $transferObject->getClientConfig();

        $this->api->logRequest($requestBody, self::LOG_NAME);
        $transaction = $this->api->refund()->execute(
            $requestBody,
            $config['order_id'],
            $config['status'],
            $config['store_id']
        );
        $this->api->logResponse($transaction, self::LOG_NAME);

        $statusCode = $transaction['status'] ?? null;
        $status = $transaction['response']['status'] ?? $statusCode;
        $statusType = is_array($status) && isset($status['type']) ? $status['type'] : null;
        $async = $statusType === Api::STATUS_FAILED || $statusCode >= 300;

        $this->api->saveRequest(
            $requestBody,
            $transaction['response'],
            $statusCode,
            self::LOG_NAME,
            $async
        );

        return ['status' => $status, 'status_code' => $statusCode, 'transaction' => $transaction['response']];
    }
}
