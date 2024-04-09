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
 * @package     Biz_Core
 * @copyright   Copyright (c) Biz (https://www.bizcommerce.com.br/)
 * @license     https://www.bizcommerce.com.br/LICENSE.txt
 */

/*global define*/
define([
    'jquery',
    'mage/translate',
    'Koin_Payment/js/model/credit-card-validation/credit-card-number-validator',
    'validation'
], function ($, $t, creditCardNumberValidator) {
    'use strict';
    $.validator.addMethod(
        'validate-koin-card-number',
        function (number) {
            return creditCardNumberValidator(number).isValid;
        },
        $t('Please enter a valid credit card type number.')
    );
});
