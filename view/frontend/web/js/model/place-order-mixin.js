define([
    'mage/storage',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/model/payment/place-order-hooks',
    'underscore',
    'jquery',
    'mage/url'
], function (storage, errorProcessor, fullScreenLoader, customerData, hooks, _, $, urlBuilder) {
    'use strict';

    return function (placeOrderAction) {
        return function (serviceUrl, payload, messageContainer) {
            var method = payload?.paymentMethod?.method;

            if (!method || !method.includes('koin') || method === 'koin_redirect') {
                return placeOrderAction(serviceUrl, payload, messageContainer);
            }

            var headers = {}, redirectURL = '';

            fullScreenLoader.startLoader();
            _.each(hooks.requestModifiers, function (modifier) {
                modifier(headers, payload);
            });

            return storage.post(
                serviceUrl, JSON.stringify(payload), true, 'application/json', headers
            ).fail(
                function (response) {
                    errorProcessor.process(response, messageContainer);
                    redirectURL = response.getResponseHeader('errorRedirectAction');
                    if (window.KoinPopup) {
                        var formKey = $.mage.cookies.get('form_key');
                        var platform = 'Magento';

                        if (!formKey) {
                            formKey = $('input[name="form_key"]').val();
                        }

                        $.ajax({
                            url: urlBuilder.build('koin/bnplmodal/config'),
                            type: 'GET',
                            dataType: 'json',
                            data: {
                                form_key: formKey
                            },
                            async: false,
                            showLoader: true,
                            success: function (response) {
                                if (response.success && response.config) {
                                    KoinPopup.init({
                                        plataforma: platform,
                                        showContainerCustom: true,
                                    });

                                    KoinPopup.openModal({
                                        loja: response.config.storeName || 'Magento Store',
                                        parcelas: response.config.installments || 6,
                                        onConfirm: function () {
                                            $('#koin_redirect').click();
                                        }
                                    });
                                }
                            }
                        });
                        return;
                    }

                    if (redirectURL) {
                        setTimeout(function () {
                            errorProcessor.redirectTo(redirectURL);
                        }, 3000);
                    }
                }
            ).done(
                function (response) {
                    var clearData = {
                        'selectedShippingAddress': null,
                        'shippingAddressFromData': null,
                        'newCustomerShippingAddress': null,
                        'selectedShippingRate': null,
                        'selectedPaymentMethod': null,
                        'selectedBillingAddress': null,
                        'billingAddressFromData': null,
                        'newCustomerBillingAddress': null
                    };

                    if (response.responseType !== 'error') {
                        customerData.set('checkout-data', clearData);
                    }
                }
            ).always(
                function () {
                    fullScreenLoader.stopLoader();
                    _.each(hooks.afterRequestListeners, function (listener) {
                        listener();
                    });
                }
            );
        };
    };
});
