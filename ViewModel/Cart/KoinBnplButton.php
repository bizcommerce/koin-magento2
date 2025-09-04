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

namespace Koin\Payment\ViewModel\Cart;

use Koin\Payment\Helper\Data;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Data\Form\FormKey;
use Magento\Customer\Model\Session as CustomerSession;

/**
 * ViewModel for BNPL Button on Cart Page
 */
class KoinBnplButton implements ArgumentInterface
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
     * @var FormKey
     */
    private $formKey;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @param Data $koinHelper
     * @param UrlInterface $urlBuilder
     * @param Repository $assetRepo
     * @param JsonHelper $jsonHelper
     * @param FormKey $formKey
     * @param CustomerSession $customerSession
     */
    public function __construct(
        Data $koinHelper,
        UrlInterface $urlBuilder,
        Repository $assetRepo,
        JsonHelper $jsonHelper,
        FormKey $formKey,
        CustomerSession $customerSession
    ) {
        $this->koinHelper = $koinHelper;
        $this->urlBuilder = $urlBuilder;
        $this->assetRepo = $assetRepo;
        $this->jsonHelper = $jsonHelper;
        $this->formKey = $formKey;
        $this->customerSession = $customerSession;
    }

    /**
     * Check if BNPL button should be displayed on cart
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->koinHelper->getConfig('active') &&
               $this->koinHelper->getConfig('show_bnpl_cart_banner');
    }

    /**
     * Get Koin logo URL
     *
     * @return string
     */
    public function getLogoUrl(): string
    {
        try {
            return $this->assetRepo->getUrl('Koin_Payment::images/logo.svg');
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
     * Check if customer is logged in
     *
     * @return bool
     */
    public function isCustomerLoggedIn(): bool
    {
        return $this->customerSession->isLoggedIn();
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
     * Get JSON configuration for JavaScript
     *
     * @return string
     */
    public function getJsonConfig(): string
    {
        $config = [
            'checkoutUrl' => $this->getCheckoutUrl(),
            'isLoggedIn' => $this->isCustomerLoggedIn(),
            'guestCheckoutAllowed' => $this->isGuestCheckoutAllowed(),
            'formKey' => $this->getFormKey()
        ];

        return $this->jsonHelper->jsonEncode($config);
    }
}