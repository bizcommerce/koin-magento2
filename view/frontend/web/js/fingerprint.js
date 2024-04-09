/*global define*/
define([
    'jquery'
], function ($) {
    'use strict';

    $.widget('koin.fingerprint', {
        options: {
            "fingerprint_url": '',
            "fingerprint_id": '',
            "org_id": ''
        },

        _create: function() {
            var self = this;
            this.callFingerprintScript();
        },

        callFingerprintScript: function () {
            var self = this;
            if (typeof window.koinFingerPrintLoaded == 'undefined') {
                let scriptLoaded = this.loadScriptAsync(self.options.fingerprint_url);
                scriptLoaded.then(function () {
                    setSessionID(self.options.fingerprint_id);
                    window.koinFingerPrintLoaded = true;
                });
            }
        },

        loadScriptAsync: function(uri){
            var self = this;
            return new Promise((resolve, reject) => {
                let script = document.createElement('script');
                script.src = uri;
                script.id = "deviceId_fp";
                script.async = true;
                if (self.options.org_id.length > 0) {
                    script.setAttribute('org_id',  self.options.org_id);
                }
                script.onload = () => {
                    resolve();
                };
                let firstScriptTag = document.getElementsByTagName('script')[0];
                firstScriptTag.parentNode.insertBefore(script, firstScriptTag);
            });
        }
    });
    return $.koin.fingerprint;
});

