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

namespace Koin\Payment\Gateway\Http\Client\Risk;

use Koin\Payment\Gateway\Http\Client\Risk\Api\Evaluation;
use Koin\Payment\Helper\Data;

class Api
{
    const STATUS_DENIED = 'denied';
    const STATUS_APPROVED = 'approved';
    const STATUS_RECEIVED = 'received';
    const STATUS_QUEUED = 'queued';
    const STATUS_ERROR = 'error';
    const STATUS_ABORTED = 'aborted';

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var Evaluation
     */
    private $evaluation;

    /**
     * @param Data $helper
     * @param Evaluation $evaluation
     */
    public function __construct(
        Data $helper,
        Evaluation $evaluation
    ) {
        $this->helper = $helper;
        $this->evaluation = $evaluation;
    }

    /**
     * @return Evaluation
     */
    public function evaluation(): Evaluation
    {
        return $this->evaluation;
    }

    /**
     * @return array
     */
    public function getStatuses(): array
    {
        return [
            self::STATUS_APPROVED => __(self::STATUS_APPROVED),
            self::STATUS_DENIED => __(self::STATUS_DENIED),
            self::STATUS_RECEIVED => __(self::STATUS_RECEIVED),
        ];
    }

    /**
     * @param $request
     * @param string $name
     */
    public function logRequest($request, $name = 'koin-risk'): void
    {
        $this->helper->log('Request', $name);
        $this->helper->log($request, $name);
    }

    /**
     * @param $response
     * @param string $name
     */
    public function logResponse($response, $name = 'koin-risk'): void
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
    public function saveRequest($request, $response, $statusCode): void
    {
        $this->helper->saveRequest($request, $response, $statusCode, \Koin\Payment\Model\Antifraud::RESOURCE_CODE);
    }
}
