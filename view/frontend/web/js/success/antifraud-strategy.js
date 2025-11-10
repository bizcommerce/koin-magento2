define([
    'jquery',
    'Magento_Ui/js/modal/modal'
], function ($, modal) {
    'use strict';

    return function (config) {
        const modalId = '#koin-modal-antifraud-strategy',
            modalElem = $(modalId);
        let eventSource;

        const koinModal = {
            init: function () {
                $.ajax({
                    url: config.antifraudStrategyUrl,
                    type: 'GET',
                    dataType: 'json',
                    showLoader: false,
                    success: (response) => {
                        const isApproved = response?.is_approved === true;
                        if (!isApproved) {
                            this.setupModal();
                            this.setupSSE();
                        }
                    },
                    error: (error) => {
                        console.log(error);
                    }
                });
            },

            setupModal: function () {
                const options = {
                    type: 'popup',
                    modalClass: 'koin-modal-antifraud-strategy',
                    responsive: true,
                    innerScroll: false,
                    buttons: [{
                        text: $.mage.__('Close'),
                        class: 'action secondary',
                        click: () => this.closeModal()
                    }]
                };

                modal(options, $(modalId));
                $(modalId).modal('openModal');

                modalElem.on('modalclosed', () => {
                    this.destroySSE();
                });
            },

            setupSSE: function () {
                eventSource = new EventSource(config.antifraudStrategyUrl.slice(0, -1) + '?SSE=true');

                eventSource.addEventListener('koin-payment-antifraud-strategy', (event) => {
                    const data = JSON.parse(event.data);
                    if (data?.is_approved) {
                        this.closeModal();
                        $(document.body).trigger('processStop');
                    }
                });

                eventSource.onerror = (e) => {
                    console.log('SSE connection error', e);
                    this.destroySSE();
                };
            },

            closeModal: function () {
                modalElem.modal('closeModal');
                this.destroySSE();
            },

            destroySSE: function () {
                if (eventSource) {
                    eventSource.close();
                }
            }
        };

        koinModal.init();
    };
});
