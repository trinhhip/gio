/**
 * Cookie bar logic
 */

define([
    'uiCollection',
    'jquery',
    'uiRegistry',
    'underscore',
    'mage/translate',
    'Amasty_GdprCookie/js/model/cookie',
    'Magento_Ui/js/modal/modal',
    'Amasty_GdprCookie/js/action/create-modal',
    'Amasty_GdprCookie/js/action/information-modal',
    'Amasty_GdprCookie/js/action/save',
    'Amasty_GdprCookie/js/action/allow',
    'Amasty_GdprCookie/js/model/cookie-data-provider',
    'Amasty_GdprCookie/js/model/manageable-cookie',
    'Amasty_GdprCookie/js/storage/essential-cookie'
], function (
    Collection,
    $,
    registry,
    _,
    $t,
    cookieModel,
    modal,
    createModal,
    informationModal,
    actionSave,
    actionAllow,
    cookieDataProvider,
    manageableCookie,
    essentialStorage
) {
    'use strict';

    return Collection.extend({
        defaults: {
            template: 'Amasty_GdprCookie/cookiebar',
            allowLink: '/',
            firstShowProcess: '0',
            cookiesName: [],
            domainName: '',
            barSelector: '[data-amcookie-js="bar"]',
            settingsFooterLink: '[data-amcookie-js="footer-link"]',
            settingsGdprLink: '[data-amgdpr-js="cookie-link"]',
            showClass: '-show',
            setupModalTitle: $t('Please select and accept your Cookies Group'),
            isScrollBottom: false,
            isShowNotificationBar: false,
            isPopup: false,
            isDeclineEnabled: false,
            barLocation: null,
            names: {
                setupModal: '.setup-modal',
                cookieTable: '.cookie-table'
            },
            popup: {
                cssClass: 'amgdprcookie-groups-modal'
            },
            setupModal: null
        },

        initialize: function () {
            this._super();

            cookieDataProvider.getCookieData().fail(function () {
                manageableCookie.setForce(true);
                manageableCookie.processManageableCookies();
            }).done(function (cookieData) {
                manageableCookie.updateGroups(cookieData);
                manageableCookie.processManageableCookies();
                essentialStorage.update(cookieData.groupData);
                this.isShowNotificationBar(cookieModel.isShowNotificationBar(
                    this.firstShowProcess,
                    cookieData.lastUpdate
                ));

                cookieModel.deleteDisallowedCookie();
                cookieModel.initEventHandlers();
                this.initSettingsLink();
            }.bind(this));

            return this;
        },

        initObservable: function () {
            this._super()
                .observe({
                    isScrollBottom: false,
                    isShowNotificationBar: false
                });

            return this;
        },

        /**
         * Create click event on settings links
         */
        initSettingsLink: function () {
            var elem = $(this.settingsFooterLink + ',' + this.settingsGdprLink);

            $(elem).addClass(this.showClass).on('click', function (event) {
                event.preventDefault();
                cookieDataProvider.getCookieData().done(function (cookieData) {
                    if (this.setupModal) {
                        this.setupModal.items(cookieData.groupData);
                    }

                    this.openModal();
                }.bind(this));
            }.bind(this));
        },

        /**
         * On save callback
         * @param {Object} element
         * @param {Object} modalContext
         */
        saveCookie: function (element, modalContext) {
            this._performSave(element, modalContext);
        },

        /**
         * Open Setup Cookie Modal
         */
        openModal: function () {
            if (!this.setupModal) {
                this.getModalData();

                return;
            }

            this.setupModal.openModal();
        },

        /**
         * Get Setup Modal Data
         */
        getModalData: function () {
            cookieDataProvider.getCookieData().done(function (cookieData) {
                this.initModal(cookieData.groupData);
            }.bind(this));
        },

        /**
         * Create Setup Modal Component
         */
        initModal: function (data) {
            createModal.call(
                this,
                data,
                '',
                this.popup.cssClass,
                false,
                'Amasty_GdprCookie/cookie-settings',
                this.name + this.names.setupModal,
                this.setupModalTitle
            );

            registry.async(this.name + this.names.setupModal)(function (modal) {
                this.setupModal = modal;
            }.bind(this));
        },

        /**
         * Create/Open Information Modal Component.
         */
        getInformationModal: function (data) {
            informationModal.call(this, this.names.cookieTable, data, this.popup.cssClass);
        },

        /**
         * On allow all cookies callback
         */
        allowCookies: function () {
            actionAllow().done(function () {
                $(this.barSelector).remove();
                cookieModel.triggerAllow();
            }.bind(this));
        },

        detectScroll: function () {
            if (this.barLocation == 1 || this.isPopup) {
                return;
            }

            this.elementBar = $(this.barSelector);
            $(window).on('scroll', _.throttle(this.scrollBottom, 200).bind(this));
        },

        scrollBottom: function () {
            var scrollHeight = window.innerHeight + window.pageYOffset,
                pageHeight = document.documentElement.scrollHeight;

            if (scrollHeight >= pageHeight - this.elementBar.innerHeight()) {
                this.isScrollBottom(true);

                return;
            }

            this.isScrollBottom(false);
        },

        declineCookie: function (element, modalContext) {
            var formData = cookieModel.getEssentialGroups();

            this._performSave(element, modalContext, formData);
        },

        _performSave: function (element, modalContext, formData) {
            actionSave(element, formData).done(function () {
                if (modalContext.closeModal) {
                    modalContext.closeModal();
                }
            });

            $(this.barSelector).remove();
        }
    });
});
