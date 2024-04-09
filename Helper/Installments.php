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

namespace Koin\Payment\Helper;

use Koin\Payment\Model\InstallmentsRules\Validator;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Installments data helper, prepared for Koin Transparent
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Installments extends AbstractHelper
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /** @var Validator */
    protected $ruleValidator;

    public function __construct(
        Context $context,
        PriceCurrencyInterface $priceCurrency,
        Validator $ruleValidator,
        Data $helper
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->helper = $helper;
        $this->ruleValidator = $ruleValidator;
        parent::__construct($context);
    }

    public function getAllInstallments(float $total = 0, string $ccNumber = '', int $storeId = 0): array
    {
        $allInstallments = $this->getDefaultInstallments($total);
        try {
            $rules = $this->ruleValidator->getRules($total, $ccNumber, $storeId);
            if ($rules->count() > 0) {
                /** @var \Koin\Payment\Model\InstallmentsRules $rule */
                foreach ($rules as $rule) {
                    $interestType = $this->helper->getCcConfig('interest_type');

                    $allInstallments = $this->getInstallments(
                        $allInstallments,
                        $rule->getMinimumInstallmentAmount(),
                        $total,
                        $rule->getMaxInstallments() ?: 1,
                        $rule->getMaxInstallmentsWithoutInterest() ?: 1,
                        $rule->getMinInstallments() ?: 1,
                        $rule->getHasInterest(),
                        $rule->getInterestRate(),
                        $rule->getInterestType() ?: $interestType,
                        $rule->getId()
                    );
                }

                ksort($allInstallments);
                return array_values($allInstallments);
            }
        } catch (\Exception $e) {
            $this->logError($e->getMessage());
        }

        return array_values($allInstallments);
    }

    public function getDefaultInstallments(float $total = 0): array
    {
        $allInstallments = [];
        try {
            if ($total > 0) {
                $minimumInstallments = (int) $this->helper->getCcConfig('min_installments') ?: 1;
                $maxInstallments = (int) $this->helper->getCcConfig('max_installments') ?: 1;
                $minInstallmentAmount = (float) $this->helper->getCcConfig('minimum_installment_amount');
                $hasInterest = (bool) $this->helper->getCcConfig('has_interest');
                $installmentsWithoutInterest = $this->getInstallmentsWithoutInterest();
                $interestType = $this->helper->getCcConfig('interest_type');
                $defaultInterestRate = (float) $this->helper->getCcConfig('interest_rate');

                $allInstallments = $this->getInstallments(
                    $allInstallments,
                    $minInstallmentAmount,
                    $total,
                    $maxInstallments,
                    $installmentsWithoutInterest,
                    $minimumInstallments,
                    $hasInterest,
                    $defaultInterestRate,
                    $interestType
                );
            }
        } catch (\Exception $e) {
            $this->logError($e->getMessage());
        }

        return $allInstallments;
    }

    public function getInstallmentItem(
        int $installments,
        float $interestRate,
        float $value,
        float $total,
        int $ruleId = 0
    ): array {
        return [
            'installments' => $installments,
            'interest_rate' => $interestRate,
            'installment_price' => $value,
            'total' => $total,
            'formatted_installments_price' => $this->priceCurrency->format($value, false),
            'formatted_total' => $this->priceCurrency->format($total, false),
            'rule' => $ruleId,
            'text' => $this->getInterestText($installments, $value, $interestRate, $total)
        ];
    }

    public function getInterestText(int $installments, float $value, float $interestRate, float $grandTotal): string
    {
        if ($interestRate > 0) {
            $interestText = __('with interest');
        } elseif ($interestRate < 0) {
            $interestText = __('with discount');
        } else {
            $interestText = __('without interest');
        }

        return __(
            '%1x of %2 (%3). Total: %4',
            $installments,
            $this->priceCurrency->format($value, false),
            $interestText,
            $this->priceCurrency->format($grandTotal, false)
        );
    }

    public function getInstallmentPrice(
        float $total,
        int $installment,
        bool $hasInterest,
        float $interestRate,
        string $interestType
    ): float {
        $installmentAmount = $total / $installment;
        try {
            if ($hasInterest && $interestRate > 0) {
                switch ($interestType) {
                    case 'price':
                        //Amortization with price table
                        $part1 = $interestRate * pow((1 + $interestRate), $installment);
                        $part2 = pow((1 + $interestRate), $installment) - 1;
                        $installmentAmount = round($total * ($part1 / $part2), 2);
                        break;
                    case 'compound':
                        //M = C * (1 + i)^n
                        $installmentAmount = ($total * pow(1 + $interestRate, $installment)) / $installment;
                        break;
                    case 'simple':
                        //M = C * ( 1 + ( i * n ) )
                        $installmentAmount = ($total * (1 + ($installment * $interestRate))) / $installment;
                        break;
                    case 'per_installments':
                        $installmentAmount = ($total * (1 + $interestRate)) / $installment;
                        break;
                }
            }
        } catch (\Exception $e) {
            $this->logError($e->getMessage());
        }

        return round($installmentAmount, 2);
    }

    public function getInterestRateByInstallment(
        int $installment,
        float $interestRate = 0,
        string $interestType = '',
        int $installmentsWithoutInterest = 0
    ): float {
        if ($installment > $installmentsWithoutInterest) {
            if ($interestType == 'per_installments') {
                $interestRate = (float)$this->helper->getCcConfig('interest_' . $installment . '_installments');
            }
            return $interestRate / 100;
        }

        return 0;
    }

    public function getInstallmentsWithoutInterest(): int
    {
        return (int) $this->helper->getCcConfig('max_installments_without_interest') ?: 1;
    }

    public function getInstallments(
        array $allInstallments,
        float $minInstallmentAmount,
        float $total,
        int $maxInstallments,
        int $installmentsWithoutInterest,
        int $minimumInstallments,
        bool $hasInterest,
        float $defaultInterestRate,
        mixed $interestType,
        int $ruleId = 0
    ): array {
        if ($minInstallmentAmount > 0) {
            while ($maxInstallments > ($total / $minInstallmentAmount)) {
                $maxInstallments--;
            }

            while ($installmentsWithoutInterest > ($total / $minInstallmentAmount)) {
                $installmentsWithoutInterest--;
            }
        }

        if ($minimumInstallments > 1) {
            $allInstallments[1] = $this->getFirstInstallment($total);
        }

        $maxInstallments = ($maxInstallments == 0) ? 1 : $maxInstallments;
        for ($i = $minimumInstallments; $i <= $maxInstallments; $i++) {
            $interestRate = $this->getInterestRateByInstallment(
                $i,
                $defaultInterestRate,
                $interestType,
                $installmentsWithoutInterest
            );
            $value = $this->getInstallmentPrice($total, $i, $hasInterest, $interestRate, $interestType);
            $grandTotal = $total;
            if ($hasInterest && $interestRate > 0) {
                $grandTotal = round($value * $i, 2);
            }
            $allInstallments[$i] = $this->getInstallmentItem($i, $interestRate, $value, $grandTotal, $ruleId);
        }
        return $allInstallments;
    }

    public function getFirstInstallment(float $total): array
    {
        return $this->getInstallmentItem(
            1,
            $this->getInterestRateByInstallment(1),
            $total,
            $total
        );
    }

    public function getRuleIdByOrderData(
        int $installments,
        float $total,
        string $ccNumber = '',
        int $storeId = 0
    ): int {
        try {
            $allInstallments = $this->getAllInstallments($total, $ccNumber, $storeId);
            foreach ($allInstallments as $installmentItem) {
                if ($installmentItem['installments'] == $installments) {
                    return $installmentItem['rule'];
                }
            }
        } catch (\Exception $e) {
            $this->logError($e->getMessage());
        }
        return 0;
    }

    protected function logError(string $message): void
    {
        $this->_logger->error($message);
    }
}
