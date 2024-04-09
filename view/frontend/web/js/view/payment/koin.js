define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
], function (
    Component,
    rendererList
) {
    'use strict';

    rendererList.push({
        type: 'koin_redirect',
        component: 'Koin_Payment/js/view/payment/method-renderer/redirect'
    });

    rendererList.push({
        type: 'koin_pix',
        component: 'Koin_Payment/js/view/payment/method-renderer/pix'
    });

    rendererList.push({
        type: 'koin_cc',
        component: 'Koin_Payment/js/view/payment/method-renderer/cc'
    });

    return Component.extend({});
});
