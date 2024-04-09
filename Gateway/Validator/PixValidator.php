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

namespace Koin\Payment\Gateway\Validator;

use Koin\Payment\Gateway\Http\Client\Payments\Api;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;

class PixValidator extends AbstractValidator
{
    /**
     * Performs validation of result code
     *
     * @param $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        if (!isset($validationSubject['response']) || !is_array($validationSubject['response'])) {
            throw new \InvalidArgumentException('Response does not exist');
        }

        $response = $validationSubject['response'];
        $transaction = $response['transaction'];

        if ($this->isSuccessfulTransaction($response, $transaction)) {
            return $this->createResult(true, []);
        } else {
            $error = __('The transaction is taking longer than expected, please try again in a few moments');
            return $this->createResult(false, [$error]);
        }
    }

    /**
     * @param $response
     * @return bool
     */
    private function isSuccessfulTransaction($response, $transaction)
    {
        if (isset($response['status_code']) && $response['status_code'] == 200) {
            if (
                isset($transaction['status']['type'])
                && in_array($transaction['status']['type'], $this->deniedStatus())
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array
     */
    private function deniedStatus()
    {
        return [
             Api::STATUS_OPENED,
             Api::STATUS_FAILED,
             Api::STATUS_CANCELLED
        ];
    }
}
