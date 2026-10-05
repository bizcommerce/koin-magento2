define([
    'Koin_Payment/js/model/payment-message-registry'
], function (paymentMessageRegistry) {
    'use strict';

    return function (target) {
        return target.extend({
            /** @inheritdoc */
            initChildren: function () {
                this._super();

                if (this.item && this.item.method && this.item.method.includes('koin')) {
                    paymentMessageRegistry.set(this.item.method, this.messageContainer);
                }

                return this;
            }
        });
    };
});
