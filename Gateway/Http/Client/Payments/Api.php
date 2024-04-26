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

namespace Koin\Payment\Gateway\Http\Client\Payments;

use Koin\Payment\Gateway\Http\Client\Payments\Api\Create;
use Koin\Payment\Gateway\Http\Client\Payments\Api\Query;
use Koin\Payment\Gateway\Http\Client\Payments\Api\Refund;
use Koin\Payment\Gateway\Http\Client\Payments\Api\Capture;
use Koin\Payment\Gateway\Http\Client\Payments\Api\Tokenize;
use Koin\Payment\Gateway\Http\Client\Payments\Api\Notification;
use Koin\Payment\Helper\Data;

class Api
{
    public const STATUS_OPENED = 'Opened';
    public const STATUS_PUBLISHED = 'Published';
    public const STATUS_AUTHORIZED = 'Authorized';
    public const STATUS_UNKNOWN = 'Unknown';
    public const STATUS_WAITING = 'Waiting';

    public const STATUS_PENDING = 'Pending';
    public const STATUS_COLLECTED = 'Collected';
    public const STATUS_REFUNDED = 'Refunded';
    public const STATUS_CANCELLED = 'Cancelled';
    public const STATUS_VOIDED = 'Voided';
    public const STATUS_FAILED = 'Failed';

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var Create
     */
    private $create;

    /**
     * @var Refund
     */
    private $refund;

    /**
     * @var Capture
     */
    private $capture;

    /**
     * @var Tokenize
     */
    private $tokenize;

    /**
     * @var Query
     */
    private $query;

    private $notification;

    public function __construct(
        Data $helper,
        Create $create,
        Capture $capture,
        Refund $refund,
        Tokenize $tokenize,
        Query $query,
        Notification $notification
    ) {
        $this->helper = $helper;
        $this->create = $create;
        $this->capture = $capture;
        $this->refund = $refund;
        $this->tokenize = $tokenize;
        $this->query = $query;
        $this->notification = $notification;
    }

    public function create(): Create
    {
        return $this->create;
    }

    public function query(): Query
    {
        return $this->query;
    }

    public function refund(): Refund
    {
        return $this->refund;
    }

    public function capture(): Capture
    {
        return $this->capture;
    }

    public function tokenize(): Tokenize
    {
        return $this->tokenize;
    }

    public function notification(): Notification
    {
        return $this->notification;
    }

    /**
     * @param $request
     * @param string $name
     */
    public function logRequest($request, $name = 'koin-pix'): void
    {
        $this->helper->log('Request', $name);
        $this->helper->log($request, $name);
    }

    /**
     * @param $response
     * @param string $name
     */
    public function logResponse($response, $name = 'koin-pix'): void
    {
        $this->helper->log('RESPONSE', $name);
        $this->helper->log($response, $name);
    }

    /**
     * @param $request
     * @param $response
     * @param $statusCode
     * @return void
     */
    public function saveRequest(
        $request,
        $response,
        $statusCode,
        $method = \Koin\Payment\Model\Ui\Pix\ConfigProvider::CODE,
        $async = false
    ): void {
        if ($async) {
            $this->helper->saveRequestAsync($request, $response, $statusCode, $method);
            return;
        }

        $this->helper->saveRequest($request, $response, $statusCode, $method);
    }
}
