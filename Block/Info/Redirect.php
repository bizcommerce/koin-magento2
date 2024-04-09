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

namespace Koin\Payment\Block\Info;

use Koin\Payment\Gateway\Http\Client;
use Magento\Framework\Phrase;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Model\Config;

class Redirect extends AbstractInfo
{
    protected $_template = 'Koin_Payment::payment/info/redirect.phtml';

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var Config
     */
    protected $paymentConfig;

    /**
     * @var  DateTime
     */
    protected $date;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * BankSlip constructor.
     * @param Context $context
     * @param ConfigInterface $config
     * @param Config $paymentConfig
     * @param PriceCurrencyInterface $priceCurrency
     * @param DateTime $date
     * @param array $data
     */
    public function __construct(
        Context $context,
        ConfigInterface $config,
        Config $paymentConfig,
        PriceCurrencyInterface $priceCurrency,
        DateTime $date,
        array $data = []
    ) {
        parent::__construct($context, $config, $paymentConfig, $data);
        $this->paymentConfig = $paymentConfig;
        $this->date = $date;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @return Phrase
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTitle()
    {
        $title = __('Seu pedido de crédito está sendo processado!');
        $payment = $this->getInfo();
        $status = $payment->getAdditionalInformation('status');
        if ($status == 'denied') {
            $title = __('Seu pedido foi cancelado!');
        } elseif ($status == 'approved') {
            $title = __('Seu pedido foi confirmado com sucesso!');
        }

        return $title;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDescription(): string
    {
        $payment = $this->getInfo();
        $paymentModel = $payment->getMethodInstance();
        $status = $payment->getAdditionalInformation('status');

        $description = $paymentModel->getConfigData('description_order_review');
        if ($status == Client::STATUS_DENIED) {
            $description = $paymentModel->getConfigData('description_order_denied');
        } elseif ($status == Client::STATUS_APPROVED) {
            $description = $paymentModel->getConfigData('description_order_approved');
            $description .= '<br>';
            $description .= __('We\'ll send you a confirmation email with informations about your account.');
        }

        return $description;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getReason(): string
    {
        $reason = '';
        $payment = $this->getInfo();
        $paymentModel = $payment->getMethodInstance();
        $status = $payment->getAdditionalInformation('status');
        if ($status == 'undefined') {
            $statusReason = $payment->getAdditionalInformation('status_reason');
            if ($statusReason) {
                switch ($statusReason) {
                    case Client::STATUS_REASON_EMAIL_VALIDATION:
                        $reason = $paymentModel->getConfigData('text_email_validation');
                        break;
                    case Client::STATUS_REASON_PROVIDER_REVIEW:
                        $reason = $paymentModel->getConfigData('text_provider_review');
                        break;
                    case Client::STATUS_REASON_FIRST_PAYMENT:
                        $reason = $paymentModel->getConfigData('text_waiting_first_payment');
                        break;
                    default:
                        break;
                }
            }
        }

        return $reason;
    }
}
