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

namespace Koin\Payment\Model;

use Koin\Payment\Api\Data\InstallmentsRulesInterface;
use Magento\Framework\Model\AbstractModel;

class InstallmentsRules extends AbstractModel implements InstallmentsRulesInterface
{
    /**
     * CMS page cache tag.
     */
    public const CACHE_TAG = 'koin_installments_rules';

    /**
     * @var string
     */
    protected $_cacheTag = 'koin_installments_rules';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'koin_installments_rules';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Koin\Payment\Model\ResourceModel\InstallmentsRules::class);
    }

    public function getTitle(): string
    {
        return (string) $this->getData(self::TITLE);
    }

    public function setTitle(string $title): void
    {
        $this->setData(self::TITLE, $title);
    }

    public function getDescription(): string
    {
        return (string) $this->getData(self::DESCRIPTION);
    }

    public function setDescription(string $description): void
    {
        $this->setData(self::DESCRIPTION, $description);
    }

    public function getShowInstallments(): bool
    {
        return (bool) $this->getData(self::SHOW_INSTALLMENTS);
    }

    public function setShowInstallments(bool $showInstallments): void
    {
        $this->setData(self::SHOW_INSTALLMENTS, $showInstallments);
    }

    public function getAccountNumber(): string
    {
        return (string) $this->getData(self::ACCOUNT_NUMBER);
    }

    public function setAccountNumber(string $accountNumber): void
    {
        $this->setData(self::ACCOUNT_NUMBER, $accountNumber);
    }

    /**
     * Get PaymentMethods.
     *
     * @return string
     */
    public function getPaymentMethods(): string
    {
        return (string) $this->getData(self::PAYMENT_METHODS);
    }

    public function setPaymentMethods(string $paymentMethods): void
    {
        $this->setData(self::PAYMENT_METHODS, $paymentMethods);
    }

    public function getExceptPaymentMethods(): string
    {
        return (string) $this->getData(self::EXCEPT_PAYMENT_METHODS);
    }

    public function setExceptPaymentMethods(string $exceptPaymentMethods): void
    {
        $this->setData(self::EXCEPT_PAYMENT_METHODS, $exceptPaymentMethods);
    }

    public function getMinInstallments(): int
    {
        return (int) $this->getData(self::MIN_INSTALLMENTS);
    }

    public function setMinInstallments(int $minInstallments): void
    {
        $this->setData(self::MIN_INSTALLMENTS, $minInstallments);
    }

    public function getMaxInstallments(): int
    {
        return (int) $this->getData(self::MAX_INSTALLMENTS);
    }

    public function setMaxInstallments(int $maxInstallments): void
    {
        $this->setData(self::MAX_INSTALLMENTS, $maxInstallments);
    }

    public function getMaxInstallmentsWithoutInterest(): int
    {
        return (int) $this->getData(self::MAX_INSTALLMENTS_WITHOUT_INTEREST);
    }

    public function setMaxInstallmentsWithoutInterest(int $maxInstallmentsWithoutInterest): void
    {
        $this->setData(self::MAX_INSTALLMENTS_WITHOUT_INTEREST, $maxInstallmentsWithoutInterest);
    }

    public function getMinimumInstallmentAmount(): float
    {
        return (float) $this->getData(self::MINIMUM_INSTALLMENT_AMOUNT);
    }

    public function setMinimumInstallmentAmount(float $minimumInstallmentAmount): void
    {
        $this->setData(self::MINIMUM_INSTALLMENT_AMOUNT, $minimumInstallmentAmount);
    }

    public function getHasInterest(): bool
    {
        return (bool) $this->getData(self::HAS_INTEREST);
    }

    public function setHasInterest(bool $hasInterest): void
    {
        $this->setData(self::HAS_INTEREST, $hasInterest);
    }

    public function getInterestType(): string
    {
        return (string) $this->getData(self::INTEREST_TYPE);
    }

    public function setInterestType(string $interestType): void
    {
        $this->setData(self::INTEREST_TYPE, $interestType);
    }

    public function getInterestRate(): float
    {
        return (float) $this->getData(self::INTEREST_RATE);
    }

    public function setInterestRate(float $interestRate): void
    {
        $this->setData(self::INTEREST_RATE, $interestRate);
    }

    public function getStartDate(): string
    {
        return (string) $this->getData(self::START_DATE);
    }

    public function setStartDate(string $startDate): void
    {
        $this->setData(self::START_DATE, $startDate);
    }

    public function getEndDate(): string
    {
        return (string) $this->getData(self::END_DATE);
    }

    public function setEndDate(string $endDate): void
    {
        $this->setData(self::END_DATE, $endDate);
    }

    public function getMinimumAmount(): float
    {
        return (float) $this->getData(self::MINIMUM_AMOUNT);
    }

    public function setMinimumAmount(float $minimumAmount): void
    {
        $this->setData(self::MINIMUM_AMOUNT, $minimumAmount);
    }

    public function getMaximumAmount(): float
    {
        return (float) $this->getData(self::MAXIMUM_AMOUNT);
    }

    public function setMaximumAmount(float $maximumAmount): void
    {
        $this->setData(self::MAXIMUM_AMOUNT, $maximumAmount);
    }

    public function getStatus(): bool
    {
        return (bool) $this->getData(self::STATUS);
    }

    public function setStatus(bool $status): void
    {
        $this->setData(self::STATUS, $status);
    }

    public function getCreatedAt(): string
    {
        return (string) $this->getData(self::CREATED_AT);
    }

    public function setCreatedAt(string $createdAt): void
    {
        $this->setData(self::CREATED_AT, $createdAt);
    }

    public function getUpdatedAt(): string
    {
        return (string) $this->getData(self::UPDATED_AT);
    }

    public function setUpdatedAt(string $updatedAt): void
    {
        $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
