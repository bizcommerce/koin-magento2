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
    'mage/url',
    'Magento_Customer/js/model/authentication-popup',
    'Magento_Customer/js/action/login',
    'Magento_Customer/js/customer-data',
    'mage/translate',
    'jquery/jquery-storageapi'
], function ($, urlBuilder, authenticationPopup, loginAction, customerData, $t) {
    'use strict';

    return function (config, element) {
        var $element = $(element),
            $button = $element.find('#koin-bnpl-banner-btn');

        /**
         * Helper function to store payment method in localStorage
         * Uses direct storage API to avoid checkout-data dependency issues
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

                checkoutData.selectedPaymentMethod = 'koin_redirect';
                storageApi.set('checkout-data', checkoutData);
                return true;
            } catch (error) {
                console.error('Koin BNPL: Error storing payment method:', error);
                return false;
            }
        }

        // Register callback for successful login
        loginAction.registerLoginCallback(function () {
            var checkoutUrl = sessionStorage.getItem('koin_checkout_url');
            if (checkoutUrl) {
                sessionStorage.removeItem('koin_checkout_url');
                // Add small delay to ensure login is fully processed
                setTimeout(function() {
                    window.location.href = checkoutUrl;
                }, 100);
            } else {
                console.warn('Koin BNPL: No checkout URL found in sessionStorage');
                // Fallback to default checkout if no URL stored
                setTimeout(function() {
                    window.location.href = config.checkoutUrl;
                }, 100);
            }
        });

        $button.on('click', function (e) {
            e.preventDefault();
            var checkoutUrl = config.checkoutUrl,
                addToCartUrl = config.addToCartUrl,
                guestAllowed = config.guestAllowed,
                isLoggedIn = customerData.get('customer')()?.firstname !== undefined,
                productId = config.productId,
                formKey = config.formKey;

            if (!productId) {
                alert($t('Product not found. Please refresh the page.'));
                return;
            }

            // Add loading state to button
            $button.prop('disabled', true)
                  .addClass('disabled loading')
                  .find('.koin-text').text($t('Adding to cart...'));

            // Step 1: Add product to cart
            var formData = {
                'product': productId,
                'qty': 1,
                'form_key': formKey
            };

            // Get additional form data from the product form if available
            var $productForm = $('#product_addtocart_form');
            if ($productForm.length) {
                var additionalData = $productForm.serializeArray();
                $.each(additionalData, function(index, field) {
                    if (field.name !== 'product' && field.name !== 'form_key') {
                        formData[field.name] = field.value;
                    }
                });
            }

            $.post(addToCartUrl, formData)
                .done(function (response) {
                    // Handle post-cart addition flow
                    if (response.success || response.backUrl || !response.error) {
                        // Update customer data sections
                        customerData.reload(['cart'], false);

                        // Set payment method in localStorage for checkout preselection
                        setKoinPaymentMethod();

                        // Check if login is needed
                        if (!isLoggedIn && !guestAllowed) {
                            // Store checkout URL for post-login redirect
                            sessionStorage.setItem('koin_checkout_url', checkoutUrl);

                            // Update button text and show login popup
                            $button.find('.koin-text').text($t('Please login to continue...'));

                            setTimeout(function() {
                                authenticationPopup.showModal();
                                // Reset button after showing popup
                                resetButton();
                            }, 500);
                        } else {
                            // Direct to checkout
                            $button.find('.koin-text').text($t('Redirecting to checkout...'));
                            window.location.href = checkoutUrl;
                        }
                    } else {
                        // Handle add to cart error
                        var errorMessage = response.error_message || response.message || $t('Unable to add product to cart.');
                        alert(errorMessage);
                        resetButton();
                    }
                })
                .fail(function (xhr) {
                    // Handle AJAX error
                    var errorMessage = $t('An error occurred while adding the product to cart. Please try again.');
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    alert(errorMessage);
                    resetButton();
                });

            // Reset button function
            function resetButton() {
                $button.prop('disabled', false)
                      .removeClass('disabled loading')
                      .find('.koin-text').text(config.originalText || $t('Allow Buy with Koin'));
            }
        });
    };
});
