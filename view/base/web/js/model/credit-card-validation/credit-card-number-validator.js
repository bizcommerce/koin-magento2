/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* @api */
define([
    'mageUtils',
    'Magento_Payment/js/model/credit-card-validation/credit-card-number-validator/luhn10-validator'
], function (utils, luhn10) {
    'use strict';

    /**
     * @param {*} isValid
     * @return {Object}
     */
    function resultWrapper(isValid) {
        return {
            isValid: isValid
        };
    }

    return function (value) {
        let valid,
            i,
            maxLength;

        if (utils.isEmpty(value)) {
            return resultWrapper(false);
        }

        value = value.replace(/\s+/g, '');

        if (!/^\d*$/.test(value)) {
            return resultWrapper(false);
        }

        //Tarjeta Naranja Bins
        const bypassBins = ["377798", "377799", "589562"];
        if (bypassBins.includes(value.substring(0, 6))) {
            return resultWrapper(true);
        }

        return resultWrapper(luhn10(value));
    };
});
