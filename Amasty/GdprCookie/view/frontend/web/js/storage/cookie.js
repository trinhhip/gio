/**
 * Cookie Storage
 */

define([], function () {
    'use strict';

    return {
        /**
         * Set Cookie
         * @param {string} name
         * @param {string} value
         * @param {Object} options
         */
        set: function (name, value, options) {
            var updatedCookie = encodeURIComponent(name) + '=' + encodeURIComponent(value),
                optionKey,
                optionValue;

            if (typeof options.expires === 'number') {
                options.expires = new Date(Date.now() + options.expires * 864e5);
            }

            if (options.expires) {
                options.expires = options.expires.toUTCString();
            }


            for (optionKey in options) {
                updatedCookie += '; ' + optionKey;
                optionValue = options[optionKey];

                if (optionValue !== true) {
                    updatedCookie += '=' + optionValue;
                }
            }

            document.cookie = updatedCookie;
        },

        /**
         * Delete Cookie
         * @param {string} name
         */
        delete: function (name) {
            this.set(name, '', {
                'max-age': -1,
                'path': '/',
                'expires': -1
            });
        }
    };
});
