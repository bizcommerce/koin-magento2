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

namespace Koin\Payment\Model\Ui\CreditCard;

use Koin\Payment\Helper\Data;
use Magento\Checkout\Model\Session;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Source;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Payment\Model\CcConfig;
use Koin\Payment\Model\Config\Source\CcType as SourceCcType;
use Magento\Payment\Model\CcGenericConfigProvider;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Locale\ResolverInterface;

/**
 * Class ConfigProvider
 */
class ConfigProvider extends CcGenericConfigProvider
{
    public const CODE = 'koin_cc';

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var CustomerSession $customerSession
     */
    protected $customerSession;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var Source
     */
    protected $assetSource;


    /**
     * @var SourceCcType
     */
    private $sourceCcType;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @var array
     */
    private array $icons = [];

    /**
     * @param Session $checkoutSession
     * @param CustomerSession $customerSession,
     * @param Data $helper
     * @param CcConfig $ccConfig
     * @param UrlInterface $urlBuilder
     * @param PaymentHelper $paymentHelper
     * @param Source $assetSource
     * @param SourceCcType $sourceCcType
     * @param EncryptorInterface $encryptor
     * @param ResolverInterface $localeResolver
     */
    public function __construct(
        Session $checkoutSession,
        CustomerSession $customerSession,
        Data $helper,
        CcConfig $ccConfig,
        UrlInterface $urlBuilder,
        PaymentHelper $paymentHelper,
        Source $assetSource,
        SourceCcType $sourceCcType,
        EncryptorInterface $encryptor,
        ResolverInterface $localeResolver
    ) {
        parent::__construct($ccConfig, $paymentHelper, [self::CODE]);
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->helper = $helper;
        $this->urlBuilder = $urlBuilder;
        $this->assetSource = $assetSource;
        $this->sourceCcType = $sourceCcType;
        $this->encryptor = $encryptor;
        $this->localeResolver = $localeResolver;
    }

    /**
     * Retrieve available credit card types
     *
     * @param string $methodCode
     * @return array
     */
    protected function getCcAvailableTypes($methodCode)
    {
        $types = $this->sourceCcType->toArray();
        $availableTypes = $this->methods[$methodCode]->getConfigData('cctypes');
        if ($availableTypes) {
            $availableTypes = explode(',', $availableTypes);
            foreach (array_keys($types) as $code) {
                if (!in_array($code, $availableTypes)) {
                    unset($types[$code]);
                }
            }
        }
        return $types;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getConfig()
    {
        $grandTotal = $this->checkoutSession->getQuote()->getGrandTotal();
        $methodCode = self::CODE;

        $customer = $this->customerSession->getCustomer();
        $customerTaxvat = ($customer && $customer->getTaxvat()) ? $customer->getTaxvat() : '';

        return [
            'payment' => [
                self::CODE => [
                    'grand_total' => $this->checkoutSession->getQuote()->getGrandTotal(),
                    'show_logo_on_checkout' => (int) $this->helper->getConfig('show_logo_on_checkout', self::CODE),
                    'customer_taxvat' => $customerTaxvat,
                    'icons' => $this->getIcons(),
                    'availableTypes' => $this->getCcAvailableTypes($methodCode),
                    'enable_pci_compliance' => (bool) $this->helper->getConfig('enable_pci_compliance', self::CODE),
                    'pci_client_key' => $this->getDecryptedPciClientKey(),
                    'pci_language' => $this->getKoinSdkLanguage(),
                    'pci_sdk_url' => $this->getPciSdkUrl()
                ],
                'ccform' => [
                    'grandTotal' => [$methodCode => $grandTotal],
                    'months' => [$methodCode => $this->getCcMonths()],
                    'years' => [$methodCode => $this->getCcYears()],
                    'hasVerification' => [$methodCode => $this->hasVerification($methodCode)],
                    'cvvImageUrl' => [$methodCode => $this->getCvvImageUrl()],
                    'urls' => [
                        $methodCode => [
                            'retrieve_installments' => $this->urlBuilder->getUrl('koin/installments/retrieve')
                        ]
                    ]
                ]
            ]
        ];
    }


    /**
     * Get icons for available payment methods
     *
     * @return array
     */
    public function getIcons(): array
    {
        if (!empty($this->icons)) {
            return $this->icons;
        }

        $types = $this->getCcAvailableTypes(self::CODE);
        foreach ($types as $code => $label) {
            if (!array_key_exists($code, $this->icons)) {
                $asset = $this->ccConfig->createAsset('Koin_Payment::images/cards/' . strtolower($code) . '.png');
                $placeholder = $this->assetSource->findSource($asset);
                if ($placeholder) {
                    list($width, $height) = getimagesize($asset->getSourceFile());
                    $this->icons[$code] = [
                        'url' => $asset->getUrl(),
                        'width' => $width,
                        'height' => $height,
                        'title' => __($label),
                    ];
                }
            }
        }

        return $this->icons;
    }

    /**
     * Get decrypted PCI client key
     *
     * @return string
     */
    private function getDecryptedPciClientKey(): string
    {
        $encryptedKey = $this->helper->getConfig('pci_client_key', self::CODE);
        if (empty($encryptedKey)) {
            return '';
        }

        try {
            return $this->encryptor->decrypt($encryptedKey);
        } catch (\Exception $e) {
            $this->helper->log($e->getMessage());
            return '';
        }
    }

    /**
     * Get Koin SDK language based on store locale
     *
     * @return string
     */
    private function getKoinSdkLanguage(): string
    {
        $locale = $this->localeResolver->getLocale();
        $allowedLanguages = ['pt', 'es', 'en'];

        // Extract language prefix (e.g., 'pt' from 'pt_BR')
        $langPrefix = substr($locale, 0, 2);

        // Return allowed language or fallback to language prefix
        return in_array($langPrefix, $allowedLanguages) ? $langPrefix : 'en';
    }

    /**
     * Get PCI SDK URL based on sandbox configuration
     *
     * @return string
     */
    private function getPciSdkUrl(): string
    {
        $configPath = $this->helper->getGeneralConfig('use_sandbox')
            ? 'pci_sdk_url_sandbox'
            : 'pci_sdk_url';
        return $this->helper->getEndpointConfig($configPath) ?: '';
    }
}
