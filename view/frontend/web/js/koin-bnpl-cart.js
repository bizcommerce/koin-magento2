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

define([
    'jquery',
    'Magento_Customer/js/model/authentication-popup',
    'Magento_Customer/js/customer-data',
    'mage/translate',
    'jquery/jquery-storageapi'
], function ($, authenticationPopup, customerData, $t) {
    'use strict';

    return function (config, element) {
        var $button = $(element);

        /**
         * Store Koin payment method selection in localStorage
         * This will pre-select the payment method when checkout loads
         */
        function setKoinPaymentMethod() {
            try {
                var storageApi = $.initNamespaceStorage('mage-cache-storage').localStorage;
                var checkoutData = storageApi.get('checkout-data') || {
                    selectedShippingAddress: null,
                    shippingAddressFromData: null,
                    newCustomerShippingAddress: null,
                    selectedShippingRate: null,
                    selectedPaymentMethod: null,
                    selectedBillingAddress: null,
                    billingAddressFromData: null,
                    newCustomerBillingAddress: null
                };

                // Set koin_redirect as the selected payment method
                checkoutData.selectedPaymentMethod = 'koin_redirect';
                storageApi.set('checkout-data', checkoutData);
                
                console.log('Koin payment method pre-selected');
                return true;
            } catch (error) {
                console.error('Error setting Koin payment method:', error);
                return false;
            }
        }

        /**
         * Handle button click event
         */
        $button.on('click', function (event) {
            var cart = customerData.get('cart'),
                customer = customerData.get('customer'),
                isLoggedIn = customer().firstname !== undefined,
                guestCheckoutAllowed = cart().isGuestCheckoutAllowed;

            event.preventDefault();

            // Check if cart is empty
            if (!cart().summary_count || cart().summary_count === 0) {
                alert($t('Your shopping cart is empty.'));
                return false;
            }

            // Pre-select Koin payment method
            setKoinPaymentMethod();

            // Check if login is required
            if (!isLoggedIn && guestCheckoutAllowed === false) {
                // Store intent for post-login redirect
                sessionStorage.setItem('koin_cart_checkout', 'true');
                
                // Show authentication popup
                authenticationPopup.showModal();
                
                // After successful login, the checkout page will automatically
                // have koin_redirect selected due to localStorage
                return false;
            }

            // Disable button to prevent double clicks
            $button.attr('disabled', true);
            
            // Update button text to show progress
            $button.find('.koin-text').html($t('Redirecting to checkout...'));

            // Redirect to checkout with Koin pre-selected
            location.href = config.checkoutUrl;
        });

        /**
         * Check if we're returning from login and should redirect to checkout
         */
        $(document).ready(function () {
            var customer = customerData.get('customer'),
                isLoggedIn = customer().firstname !== undefined;

            if (isLoggedIn && sessionStorage.getItem('koin_cart_checkout') === 'true') {
                sessionStorage.removeItem('koin_cart_checkout');
                
                // Pre-select Koin payment and redirect
                setKoinPaymentMethod();
                
                // Small delay to ensure customer data is fully loaded
                setTimeout(function() {
                    location.href = config.checkoutUrl;
                }, 100);
            }
        });
    };
});