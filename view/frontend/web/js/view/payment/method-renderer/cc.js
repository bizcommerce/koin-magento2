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
/*browser:true*/
/*global define*/

define([
        'underscore',
        'ko',
        'jquery',
        'mage/translate',
        'Magento_SalesRule/js/action/set-coupon-code',
        'Magento_SalesRule/js/action/cancel-coupon',
        'Magento_Customer/js/model/customer',
        'Magento_Payment/js/view/payment/cc-form',
        'Koin_Payment/js/model/credit-card-validation/credit-card-number-validator',
        'Magento_Payment/js/model/credit-card-validation/credit-card-data',
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/action/get-payment-information',
        'Magento_Payment/js/model/credit-card-validation/validator',
        'Magento_Checkout/js/model/payment/additional-validators',
        'mage/mage',
        'mage/validation',
        'koin/validation'
    ],
    function (
        _,
        ko,
        $,
        $t,
        setCouponCodeAction,
        cancelCouponCodeAction,
        customer,
        Component,
        cardNumberValidator,
        creditCardData,
        totals,
        getPaymentInformationAction
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Koin_Payment/payment/form/cc',
                taxvat: window.checkoutConfig.payment.koin_cc.customer_taxvat.replace(/[^0-9]/g, ""),
                creditCardOwner: '',
                creditCardInstallments: '',
                koinCreditCardNumber: '',
                creditCardType: '',
                showCardData: ko.observable(true),
                installments: ko.observableArray([]),
                hasInstallments: ko.observable(false),
                installmentsUrl: '',
                showInstallmentsWarning: ko.observable(true),
                debounceTimer: null
            },

            /** @inheritdoc */
            initObservable: function () {
                var self = this;
                this._super()
                    .observe([
                        'taxvat',
                        'creditCardType',
                        'creditCardExpYear',
                        'creditCardExpMonth',
                        'koinCreditCardNumber',
                        'creditCardType',
                        'creditCardVerificationNumber',
                        'selectedCardType',
                        'creditCardOwner',
                        'creditCardInstallments'
                    ]);

                this.creditCardVerificationNumber('');

                setCouponCodeAction.registerSuccessCallback(function() {
                    self.updateInstallmentsValues();
                });

                cancelCouponCodeAction.registerSuccessCallback(function() {
                    self.updateInstallmentsValues();
                });

                //Set credit card number to credit card data object
                this.koinCreditCardNumber.subscribe(function (value) {
                    let result;

                    self.installments.removeAll();
                    self.hasInstallments(false);
                    self.showInstallmentsWarning(true);

                    if (value === '' || value === null) {
                        return false;
                    }
                    result = cardNumberValidator(value);

                    if (!result.isValid) {
                        return false;
                    }

                    if (result.card !== null) {
                        creditCardData.creditCard = result.card;
                    }

                    if (result.isValid) {
                        creditCardData.koinCreditCardNumber = value;
                    }

                    self.updateInstallmentsValues();
                });

                return this;
            },

            logoOnCheckout: function() {
                var inputClasses = 'radio';
                if (window.checkoutConfig.payment.koin_cc.show_logo_on_checkout) {
                    inputClasses = 'radio koin-input';
                }
                return inputClasses;
            },

            getCode: function() {
                return this.item.method;
            },

            /**
             * Get data
             * @returns {Object}
             */
            getData: function () {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'taxvat': this.taxvat(),
                        'cc_cid': this.creditCardVerificationNumber(),
                        'cc_type': this.creditCardType(),
                        'cc_exp_year': this.creditCardExpYear(),
                        'cc_exp_month': this.creditCardExpMonth(),
                        'cc_number': this.koinCreditCardNumber(),
                        'cc_owner': this.creditCardOwner(),
                        'installments': this.creditCardInstallments() ? this.creditCardInstallments() : 1
                    }
                };
            },

            /**
             * Check if payment is active
             *
             * @returns {Boolean}
             */
            isActive: function() {
                return this.getCode() === this.isChecked();
            },

            /**
             * @return {Boolean}
             */
            validate: function () {
                const $form = $('#' + 'form_' + this.getCode());
                return ($form.validation() && $form.validation('isValid'));
            },

            /**
             * @returns {boolean|*}
             */
            retrieveInstallmentsUrl: function() {
                try {
                    this.installmentsUrl = window.checkoutConfig.payment.ccform.urls[this.getCode()].retrieve_installments;
                    return this.installmentsUrl;
                } catch (e) {
                    console.log('Installments URL not defined');
                }
                return false;
            },

            isLoggedIn: function() {
                return customer.isLoggedIn();
            },

            updateInstallmentsValues: function() {

                var self = this;
                if (self.koinCreditCardNumber().length > 6) {

                    if (self.debounceTimer !== null) {
                        clearTimeout(self.debounceTimer);
                    }

                    //I need to change it to a POST with body
                    self.debounceTimer = setTimeout(() => {
                        totals.isLoading(true);
                        fetch(self.retrieveInstallmentsUrl(), {
                            method: 'POST',
                            cache: 'no-cache',
                            headers: {'Content-Type': 'application/json'},
                            body: JSON.stringify({
                                form_key: window.checkoutConfig.formKey,
                                cc_number: self.koinCreditCardNumber()
                            })
                        }).then((response) => {
                            self.installments.removeAll();
                            return response.json();
                        }).then(json => {
                            json.forEach(function (installment) {
                                self.installments.push(installment);
                                self.hasInstallments(true);
                                self.showInstallmentsWarning(false);
                            });

                            getPaymentInformationAction().done(function () {
                                totals.isLoading(false);
                            });
                        });
                    }, 500);
                }
            },

            /**
             * Get list of available credit card types
             * @returns {Object}
             */
            getKoinCcAvailableTypes: function () {
                return window.checkoutConfig.payment.koin_cc.availableTypes;
            },

            /**
             * Get list of available credit card types values
             * @returns {Object}
             */
            getKoinCcAvailableTypesValues: function () {
                return _.map(this.getKoinCcAvailableTypes(), function (value, key) {
                    return {
                        'value': key,
                        'type': value
                    };
                });
            },

            /**
             * Get available credit card type by code
             * @param {String} code
             * @returns {String}
             */
            getKoinCcTypeTitleByCode: function (code) {
                var title = '',
                    keyValue = 'value',
                    keyType = 'type';

                _.each(this.getKoinCcAvailableTypesValues(), function (value) {
                    if (value[keyValue] === code) {
                        title = value[keyType];
                    }
                });

                return title;
            },

            getCcIcons: function(type) {
                return window.checkoutConfig.payment.koin_cc.icons.hasOwnProperty(type) ?
                    window.checkoutConfig.payment.koin_cc.icons[type]
                    : false;
            },

            /**
             * Get credit card details
             * @returns {Array}
             */
            getInfo: function () {
                return [
                    {
                        'name': 'Credit Card Type', value: this.getKoinCcTypeTitleByCode(this.creditCardType())
                    },
                    {
                        'name': 'Card Number', value: this.formatDisplayCcNumber(this.koinCreditCardNumber())
                    }
                ];
            }
        });
    }
);
