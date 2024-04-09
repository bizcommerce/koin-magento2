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

namespace Koin\Payment\Api\Data;

interface InstallmentsRulesInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const TITLE = 'title';
    const ACCOUNT_NUMBER = 'account_number';
    const PAYMENT_METHODS = 'payment_methods';

    const EXCEPT_PAYMENT_METHODS = 'except_payment_methods';
    const MIN_INSTALLMENTS = 'min_installments';
    const MAX_INSTALLMENTS = 'max_installments';
    const MAX_INSTALLMENTS_WITHOUT_INTEREST = 'max_installments_without_interest';
    const MINIMUM_INSTALLMENT_AMOUNT = 'minimum_installment_amount';
    const HAS_INTEREST = 'has_interest';
    const INTEREST_TYPE = 'interest_type';
    const INTEREST_RATE = 'interest_rate';
    const START_DATE = 'start_date';
    const END_DATE = 'end_date';
    const MINIMUM_AMOUNT = 'minimum_amount';
    const MAXIMUM_AMOUNT = 'maximum_amount';
    const STATUS = 'status';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    public function getTitle(): string;

    public function setTitle(string $title): void;

    public function getAccountNumber(): string;

    public function setAccountNumber(string $accountNumber);

    public function getPaymentMethods(): string;

    public function setPaymentMethods(string $paymentMethods): void;

    public function getExceptPaymentMethods(): string;

    public function setExceptPaymentMethods(string $exceptPaymentMethods): void;

    public function getMinInstallments(): int;

    public function setMinInstallments(int $minInstallments);

    public function getMaxInstallments(): int;

    public function setMaxInstallments(int $maxInstallments);

    public function getMaxInstallmentsWithoutInterest(): int;

    public function setMaxInstallmentsWithoutInterest(int $maxInstallmentsWithoutInterest): void;

    public function getMinimumInstallmentAmount(): float;

    public function setMinimumInstallmentAmount(float $minimumInstallmentAmount): void;

    public function getHasInterest(): bool;

    public function setHasInterest(bool $hasInterest): void;

    public function getInterestType(): string;

    public function setInterestType(string $interestType): void;

    public function getInterestRate(): float;

    public function setInterestRate(float $interestRate): void;

    public function getStartDate(): string;

    public function setStartDate(string $startDate): void;

    public function getEndDate(): string;

    public function setEndDate(string $endDate): void;

    public function getMinimumAmount(): float;

    public function setMinimumAmount(float $minimumAmount): void;

    public function getMaximumAmount(): float;

    public function setMaximumAmount(float $maximumAmount): void;

    public function getStatus(): bool;

    public function setStatus(bool $status): void;

    public function getCreatedAt(): string;

    public function setCreatedAt(string $createdAt): void;

    public function getUpdatedAt(): string;

    public function setUpdatedAt(string $updatedAt): void;
}
