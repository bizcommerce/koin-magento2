<?php
/**
 * Biz
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Biz.com license that is
 * available through the world-wide-web at this URL:
 * https://www.bizcommerce.com.br/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Biz
 * @package     Koin_Payment
 * @copyright   Copyright (c) Biz (https://www.bizcommerce.com.br/)
 * @license     https://www.bizcommerce.com.br/LICENSE.txt
 */

namespace Koin\Payment\Model;

class Installments
{
    /**
     * @var float
     */
    protected $total;

    /**
     * @var float
     */
    protected $originalPrice;

    /**
     * @var int
     */
    protected $maxInstallmentsWithoutInterest;

    /**
     * @var float
     */
    protected $installmentsWithoutInterestValue;

    /**
     * @var int
     */
    protected $maxInstallments;

    /**
     * @var float
     */
    protected $installmentValue;

    /**
     * @var float
     */
    protected $interestRate;

    /**
     * @var float
     */
    protected $interestAmount;

    /**
     * @return float
     */
    public function getTotal(): float
    {
        return $this->total;
    }

    /**
     * @param float $total
     */
    public function setTotal(float $total)
    {
        $this->total = $total;
    }

    /**
     * @return float
     */
    public function getOriginalPrice(): float
    {
        return $this->originalPrice;
    }

    /**
     * @param float $originalPrice
     */
    public function setOriginalPrice(float $originalPrice)
    {
        $this->originalPrice = $originalPrice;
    }

    /**
     * @return int
     */
    public function getMaxInstallmentsWithoutInterest(): int
    {
        return $this->maxInstallmentsWithoutInterest;
    }

    /**
     * @param int $maxInstallmentsWithoutInterest
     */
    public function setMaxInstallmentsWithoutInterest(int $maxInstallmentsWithoutInterest)
    {
        $this->maxInstallmentsWithoutInterest = $maxInstallmentsWithoutInterest;
    }

    /**
     * @return float
     */
    public function getInstallmentsWithoutInterestValue(): float
    {
        return $this->installmentsWithoutInterestValue;
    }

    /**
     * @param float $installmentsWithoutInterestValue
     */
    public function setInstallmentsWithoutInterestValue(float $installmentsWithoutInterestValue)
    {
        $this->installmentsWithoutInterestValue = $installmentsWithoutInterestValue;
    }

    /**
     * @return int
     */
    public function getMaxInstallments(): int
    {
        return $this->maxInstallments;
    }

    /**
     * @param int $maxInstallments
     */
    public function setMaxInstallments(int $maxInstallments)
    {
        $this->maxInstallments = $maxInstallments;
    }

    /**
     * @return float
     */
    public function getInstallmentValue(): float
    {
        return $this->installmentValue;
    }

    /**
     * @param float $installmentValue
     */
    public function setInstallmentValue(float $installmentValue)
    {
        $this->installmentValue = $installmentValue;
    }

    /**
     * @return float
     */
    public function getInterestRate(): float
    {
        return $this->interestRate;
    }

    /**
     * @param float $interestRate
     */
    public function setInterestRate(float $interestRate)
    {
        $this->interestRate = $interestRate;
    }

    /**
     * @return float
     */
    public function getInterestAmount(): float
    {
        return $this->interestAmount;
    }

    /**
     * @param float $interestAmount
     */
    public function setInterestAmount(float $interestAmount)
    {
        $this->interestAmount = $interestAmount;
    }

}
