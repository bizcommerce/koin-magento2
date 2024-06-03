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
 */

namespace Koin\Payment\Observer;

use Koin\Payment\Helper\Installments;
use Koin\Payment\Model\ResourceModel\InstallmentsRulesRepository;
use Magento\Checkout\Model\Session;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\Quote\Payment;

class CreditCardAssignObserver extends AbstractDataAssignObserver
{
    protected $brandNames = [
        'CA' => 'master',
        'VI' => 'visa',
        'AX' => 'American Express',
        'CB' => 'Carte Blanche',
        'CL' => 'Cabal',
        'CS' => 'Cencosud',
        'DC' => 'Diners',
        'DS' => 'Discover',
        'EC' => 'Elo',
        'TN' => 'Tarjeta Naranja',
        'HC' => 'HiperCard'
    ];

    /** @var Session  */
    protected $checkoutSession;

    /** @var CustomerSession $customerSession */
    protected $customerSession;

    /** @var Installments  */
    protected $installmentsHelper;

    /** @var InstallmentsRulesRepository  */
    protected $rulesRepository;

    /** @var Json  */
    protected $json;

    public function __construct(
        Session $checkoutSession,
        CustomerSession $customerSession,
        InstallmentsRulesRepository $rulesRepository,
        Installments $installmentsHelper,
        Json $json
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->rulesRepository = $rulesRepository;
        $this->installmentsHelper = $installmentsHelper;
        $this->json = $json;
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        $dataObject = $this->readDataArgument($observer);
        $additionalData = $dataObject->getData(PaymentInterface::KEY_ADDITIONAL_DATA);

        if (!is_array($additionalData)) {
            return;
        }

        if (isset($additionalData['cc_number'])) {
            $installments = $additionalData['installments'] ?? 0;
            $ccOwner = $additionalData['cc_owner'] ?? null;
            $ccType = $additionalData['cc_type'] ?? null;
            $ccLast4 = substr((string) $additionalData['cc_number'], -4);
            $ccBin = substr((string) $additionalData['cc_number'], 0, 6);
            $ccExpMonth = $additionalData['cc_exp_month'] ?? null;
            $ccExpYear = $additionalData['cc_exp_year'] ?? null;

            $this->updateInterest((int) $installments);

            /** @var Payment $paymentInfo */
            $paymentInfo = $this->readPaymentModelArgument($observer);
            $paymentInfo->addData([
                'cc_type' => $ccType,
                'cc_owner' => $ccOwner,
                'cc_number' => $additionalData['cc_number'],
                'cc_last_4' => $ccLast4,
                'cc_cid' => $additionalData['cc_cid'],
                'cc_exp_month' => $ccExpMonth,
                'cc_exp_year' => $ccExpYear
            ]);

            $this->setAdditionalInfo($paymentInfo, [
                'order_step' => 'processing',
                'cc_type' => $ccType,
                'cc_owner' => $ccOwner,
                'cc_last_4' => $ccLast4,
                'cc_exp_month' => $ccExpMonth,
                'cc_exp_year' => $ccExpYear,
                'cc_number' => $additionalData['cc_number'],
                'cc_cid' => $additionalData['cc_cid'],
                'installments' => $installments,
                'cc_installments' => $installments,
                'cc_bin' => $ccBin,
                'rule_id' => $additionalData['rule_id'] ?? 0,
                'payment_method' => $this->getBrandName($ccType)
            ]);

            $this->setRuleInformation($paymentInfo, (int) $installments, (string) $additionalData['cc_number']);
        }
    }

    protected function setAdditionalInfo($paymentInfo, $paymentData): void
    {
        foreach ($paymentData as $key => $value) {
            $paymentInfo->setAdditionalInformation($key, $value);
            $this->customerSession->setData($key, $value);
        }
    }

    protected function getBrandName(string $ccType): string
    {
        $brandName = 'Otra';
        if (isset($this->brandNames[$ccType])) {
            $brandName = $this->brandNames[$ccType];
        }
        return $brandName;
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    protected function updateInterest(int $installments): void
    {
        $this->checkoutSession->setData('koin_installments', $installments);
        $quote = $this->checkoutSession->getQuote();
        $quote->setTotalsCollectedFlag(false)->collectTotals();
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function setRuleInformation(Payment $paymentInfo, int $installments, string $ccNumber): void
    {
        $quote = $this->checkoutSession->getQuote();
        $grandTotal = $quote->getGrandTotal() - (float) $quote->getKoinInterestAmount();

        $this->setAdditionalInfo($paymentInfo, [
            'rule_id' => '',
            'rule_title' => '',
            'rule_data' => '',
            'rule_account_number' => ''
        ]);

        $ruleId = $paymentInfo->getAdditionalInformation('rule_id')
            ?: $this->installmentsHelper->getRuleIdByOrderData($installments, $grandTotal, $ccNumber);
        if ($ruleId) {
            $rule = $this->rulesRepository->getById($ruleId);
            $this->setAdditionalInfo($paymentInfo, [
                'rule_id' => $ruleId,
                'rule_data' => $this->json->serialize($rule->getData()),
                'rule_title' => $rule->getTitle(),
                'rule_account_number' => (string) trim($rule->getAccountNumber())
            ]);
        }
    }
}
