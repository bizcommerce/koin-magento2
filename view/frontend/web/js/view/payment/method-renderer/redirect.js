/*browser:true*/
/*global define*/

define(
    [
        'Magento_Checkout/js/view/payment/default',
        'mage/url'
    ],
    function (Component, url) {
        'use strict';

        return Component.extend({
            defaults: {
                redirectAfterPlaceOrder: false,
                template: 'Koin_Payment/payment/form/redirect',
                taxvat: window.checkoutConfig.payment.koin_redirect.customer_taxvat.replace(/[^0-9]/g, "")
            },

            /** @inheritdoc */
            initObservable: function () {
                this._super().observe([
                    'taxvat'
                ]);

                return this;
            },

            getCode: function() {
                return 'koin_redirect';
            },

            logoOnCheckout: function() {
                var inputClasses = 'radio';
                if (window.checkoutConfig.payment.koin_redirect.show_logo_on_checkout) {
                    inputClasses = 'radio koin-input';
                }
                return inputClasses;
            },

            useDefaultInstructions: function () {
                return window.checkoutConfig.payment.koin_redirect.use_default_checkout_instructions;
            },

            getInstructions: function () {
                return window.checkoutConfig.payment.koin_redirect.checkout_instructions;
            },

            getCheckoutImage: function () {
                return window.checkoutConfig.payment.koin_redirect.checkout_image;
            },

            getData: function() {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'taxvat': this.taxvat()
                    }
                };
            },

            afterPlaceOrder: function () {
                window.location.href = url.build('koin/redirect');
            }
        });
    }
);

