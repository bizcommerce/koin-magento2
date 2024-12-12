define([
    'jquery',
    'Magento_Ui/js/modal/modal'
], function ($, modal) {
    'use strict';

    return function (config) {
        const orderUrl = config.orderUrl;
        const modalId = '#koin-modal-success';


        var options = {
            type: 'popup',
            modalClass: 'koin-modal-success',
            responsive: true,
            innerScroll: true,
            buttons: [{
                text: $.mage.__('Close'),
                class: 'action secondary action-close',
                click: function () {
                    this.closeModal();
                    window.location.href = orderUrl;
                }
            }]
        };

        let popup = modal(options, $(modalId));
        $(modalId).modal('openModal');
    };
});
