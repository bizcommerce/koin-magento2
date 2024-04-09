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

namespace Koin\Payment\Block\Info;

use Magento\Framework\DataObject;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Model\Config;

class CreditCard extends AbstractInfo
{
    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * CreditCard constructor.
     * @param Context $context
     * @param ConfigInterface $config
     * @param Config $paymentConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        ConfigInterface $config,
        Config $paymentConfig,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        parent::__construct($context, $config, $paymentConfig, $data);
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @param \Magento\Framework\DataObject|array|null $transport
     * @return \Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        $installments = $this->getInfo()->getAdditionalInformation('cc_installments') ?: 1;

        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->getInfo()->getOrder();
        $installmentValue = $order->getGrandTotal() / $installments;

        $body = [
            (string)__('Credit Card Type') => $this->getCcTypeName(),
            (string)__('Credit Card Owner') => $this->getInfo()->getAdditionalInformation('cc_owner'),
            (string)__('Card Number') => sprintf('xxxx-%s', $this->getInfo()->getAdditionalInformation('cc_last_4')),
            (string)__('Installments') => sprintf('%s x of %s', $installments, $this->priceCurrency->format($installmentValue, false))
        ];

        $transport = new DataObject($body);

        return parent::_prepareSpecificInformation($transport);
    }

    /**
     * Retrieve credit card type name
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCcTypeName()
    {
        $types = $this->paymentConfig->getCcTypes();
        $ccType = $this->getInfo()->getCcType();
        if (isset($types[$ccType])) {
            return $types[$ccType];
        }
        return empty($ccType) ? __('N/A') : __(ucwords($ccType));
    }

}
