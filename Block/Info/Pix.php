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

use Magento\Framework\Phrase;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Model\Config;
use Magento\Sales\Api\Data\OrderInterface;

class Pix extends AbstractInfo
{
    protected $_template = 'Koin_Payment::payment/info/pix.phtml';

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

    /** @var  */
    protected $order = null;

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
     * @inheritDoc
     */
    public function _construct()
    {
        $this->setTemplate($this->_template);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getQRCode()
    {
        $payment = $this->getInfo();
        return $payment->getAdditionalInformation('qr_code');
    }

    public function getPendingStatus(): string
    {
        return \Koin\Payment\Gateway\Http\Client\Payments\Api::STATUS_PUBLISHED;
    }

    /**
     * @return OrderInterface
     */
    public function getOrder()
    {
        if (!$this->order) {
            try {
                $this->order = $this->getInfo()->getOrder();
            } catch (\Exception $e) {
                $this->_logger->error($e->getMessage());
            }
        }
        return $this->order;
    }

    public function getOrderIncrementId(): string
    {
        if ($this->getOrder()) {
            return $this->getOrder()->getIncrementId();
        }
        return '';
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getEmv()
    {
        $payment = $this->getInfo();
        return $payment->getAdditionalInformation('qr_code_emv');
    }

    /**
     * @return Phrase
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getQRCodeImage()
    {
        $payment = $this->getInfo();
        return $payment->getAdditionalInformation('qr_code_url');
    }

    public function getExpiration(): int
    {
        try {
            $expirationDateGmt = (string)$this->getInfo()->getAdditionalInformation('expiration_date');
            if ($expirationDateGmt) {
                return strtotime($expirationDateGmt);
            }
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }
        return time() + 3600;
    }

}
