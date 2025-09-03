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

namespace Koin\Payment\ViewModel\Product;

use Koin\Payment\Helper\Data;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Registry;
use Magento\Framework\Data\Form\FormKey;

/**
 * ViewModel for BNPL Banner on Product Pages
 */
class BnplBanner implements ArgumentInterface
{
    /**
     * @var Data
     */
    private $koinHelper;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var Repository
     */
    private $assetRepo;

    /**
     * @var JsonHelper
     */
    private $jsonHelper;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var FormKey
     */
    private $formKey;

    /**
     * @param Data $koinHelper
     * @param UrlInterface $urlBuilder
     * @param Repository $assetRepo
     * @param JsonHelper $jsonHelper
     * @param Registry $registry
     * @param FormKey $formKey
     */
    public function __construct(
        Data $koinHelper,
        UrlInterface $urlBuilder,
        Repository $assetRepo,
        JsonHelper $jsonHelper,
        Registry $registry,
        FormKey $formKey
    ) {
        $this->koinHelper = $koinHelper;
        $this->urlBuilder = $urlBuilder;
        $this->assetRepo = $assetRepo;
        $this->jsonHelper = $jsonHelper;
        $this->registry = $registry;
        $this->formKey = $formKey;
    }

    /**
     * Check if BNPL banner should be displayed
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->koinHelper->getConfig('active') &&
               $this->koinHelper->getConfig('show_bnpl_product_banner');
    }

    /**
     * Get banner button text
     *
     * @return string
     */
    public function getBannerText(): string
    {
        return __('Allow Buy with Koin')->render();
    }

    /**
     * Get Koin logo URL
     *
     * @return string
     */
    public function getLogoUrl(): string
    {
        try {
            return $this->assetRepo->getUrl('Koin_Payment::images/logo.png');
        } catch (LocalizedException $e) {
            return '';
        }
    }

    /**
     * Get checkout URL
     *
     * @return string
     */
    public function getCheckoutUrl(): string
    {
        return $this->urlBuilder->getUrl('checkout');
    }

    /**
     * Get login URL with checkout redirect
     *
     * @return string
     */
    public function getLoginUrl(): string
    {
        $checkoutUrl = $this->getCheckoutUrl();
        return $this->urlBuilder->getUrl('customer/account/login', [
            '_query' => ['referer' => base64_encode($checkoutUrl)]
        ]);
    }

    /**
     * Check if guest checkout is allowed
     *
     * @return bool
     */
    public function isGuestCheckoutAllowed(): bool
    {
        return $this->koinHelper->getConfig('guest_checkout', 'options', 'checkout');
    }

    /**
     * Get add to cart URL
     *
     * @return string
     */
    public function getAddToCartUrl(): string
    {
        return $this->urlBuilder->getUrl('checkout/cart/add');
    }

    /**
     * Get current product
     *
     * @return \Magento\Catalog\Model\Product|null
     */
    public function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }

    /**
     * Get current product ID
     *
     * @return int|null
     */
    public function getProductId(): ?int
    {
        $product = $this->getCurrentProduct();
        return $product ? (int)$product->getId() : null;
    }

    /**
     * Get form key for CSRF protection
     *
     * @return string
     */
    public function getFormKey(): string
    {
        try {
            return $this->formKey->getFormKey();
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Get JSON configuration for JavaScript
     *
     * @return string
     */
    public function getJsonConfig(): string
    {
        $config = [
            'checkoutUrl' => $this->getCheckoutUrl(),
            'loginUrl' => $this->getLoginUrl(),
            'addToCartUrl' => $this->getAddToCartUrl(),
            'guestAllowed' => $this->isGuestCheckoutAllowed(),
            'productId' => $this->getProductId(),
            'formKey' => $this->getFormKey()
        ];

        return $this->jsonHelper->jsonEncode($config);
    }
}
