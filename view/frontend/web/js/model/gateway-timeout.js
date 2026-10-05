define([], function () {
    'use strict';

    // Errors from a proxy in front of PHP (nginx/Cloudflare): PHP may still have finished the request
    var statuses = [0, 502, 503, 504, 524];

    return {
        /**
         * @param {Object} response - jqXHR
         * @returns {Boolean}
         */
        is: function (response) {
            return !!response && statuses.indexOf(response.status) !== -1;
        }
    };
});
