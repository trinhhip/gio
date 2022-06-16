define([
    'jquery',
    'uiComponent',
    'underscore',
    'mage/translate'
], function ($, Component, _, $t) {
    'use strict';

    return Component.extend({
        defaults: {
            cookieName: 'mst_gtm_debug',
            debugOn:    'gtm'
        },

        initialize: function () {
            this._super();

            var $body = $('body');

            var currentUrl = new URL(window.location);

            if (currentUrl.searchParams.get('debug') == this.debugOn) {
                if ($.cookie(this.cookieName) == this.debugOn) {
                    $.cookie(this.cookieName, '');
                } else {
                    $.cookie(this.cookieName, this.debugOn);
                }
            }

            if ($.cookie(this.cookieName) == this.debugOn) {
                $body.append(this.getWrapperHtml());

                this.$toolbarBody = $('.mst-gtm__toolbar-body');

                let len = 0
                setInterval(function () {
                    if (window.dataLayer.length !== len) {
                        this.updateToolbar();

                        len = window.dataLayer.length
                    }
                }.bind(this), 1000);
            }
        },

        updateToolbar: function () {
            this.$toolbarBody.html('');

            let index = 1;
            _.each(window.dataLayer, function (data) {
                if (typeof data.event != 'undefined') {
                    const $displayData = $('<pre/>')
                        .html($('<code/>').html(JSON.stringify(data, undefined, 4)));

                    const $event = $('<div />')
                        .addClass('mst-gtm__toolbar-event')
                        .append(
                            $('<strong/>').html('#' + index)
                        )
                        .append(
                            $('<i />').html('Event: ' + data.event)
                        )
                        .append(
                            $('<span />').html('Open')
                        );

                    $event.on('click', function () {
                        this.displayData($displayData)
                    }.bind(this));

                    this.$toolbarBody.append($event);

                    index++
                }
            }.bind(this));
        },

        getWrapperHtml: function () {
            return '' +
                '<div class="mst-gtm__toolbar">\n' +
                '    <strong>Google Tag Manager</strong>\n' +
                '\n' +
                '    <div class="mst-gtm__toolbar-body">\n' +
                '    </div>\n' +
                '</div>\n';
        },

        displayData: function ($data) {
            $('body .mst-gtm__toolbar-extra').remove();

            $t = $('<div/>')
                .addClass('mst-gtm__toolbar-extra')
                .html($data);

            $('body').append($t)
        }
    });
});
