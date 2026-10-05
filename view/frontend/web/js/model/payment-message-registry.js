define([], function () {
    'use strict';

    var containers = {};

    return {
        /**
         * Register the live messageContainer instance for a payment method code.
         *
         * @param {String} methodCode
         * @param {Object} container
         */
        set: function (methodCode, container) {
            containers[methodCode] = container;
        },

        /**
         * @param {String} methodCode
         * @returns {Object|undefined}
         */
        get: function (methodCode) {
            return containers[methodCode];
        }
    };
});
