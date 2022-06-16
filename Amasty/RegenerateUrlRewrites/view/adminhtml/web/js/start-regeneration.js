define([
    'Magento_Ui/js/modal/modal-component',
    'Amasty_RegenerateUrlRewrites/js/regeneration-cancel-alert',
    'jquery',
    'mage/translate',
    'underscore'
], function (Modal, CancelAlertModal, $, $t, _) {
    'use strict';

    return Modal.extend({
        defaults: {
            template: 'Amasty_RegenerateUrlRewrites/start-regeneration',
            steps: [{
                code: 'start',
                action: 'start'
            }, {
                code: 'process',
                action: 'process'
            }, {
                code: 'result',
                action: 'finish'
            }],
            regenerateTypes: [{
                index: 1,
                name: $t('Category URL’s Regeneration')
            }, {
                index: 2,
                name: $t('Products URL’s Regeneration')
            }],
            selectors: {
                form: '#config-edit-form',
                productRegeneration: '[name="groups[url_rewrites_product]'
                    + '[fields][include_product_regeneration][value]"]',
                categoriesRegeneration: '[name="groups[url_rewrites_category]'
                    + '[fields][include_category_regeneration][value]"]'
            },
            currentRegenerateType: {},
            typeIndex: 0,
            typeCounter: 0,
            maxTypes: 0,
            isShowNextButton: false,
            isDisableNextButton: true,
            isStartDisable: true,
            isStopDisable: false,
            indexedSteps: {},
            currentStep: null,
            listens: {
                currentStep: 'stepChanged'
            },
            startUrl: null,
            statusUrl: null,
            terminateUrl: null,
            messages: [],
            showLoader: false,
            typeId: 'type_id',
            processIdentity: 'console_command_regenerate',
            processStatus: $t('Regenerating...')
        },

        initialize: function () {
            this._super();

            _.each(this.steps, function (val, key) {
                this.indexedSteps[val.code] = key;
            }.bind(this));

            this.productRegenerationSelect = $(this.selectors.productRegeneration);
            this.categoriesRegenerationSelect = $(this.selectors.categoriesRegeneration);
            this.bindEvents();
            this.checkVisibleStartButton();

            return this;
        },

        initObservable: function () {
            this._super().observe([
                'currentStep',
                'currentRegenerateType',
                'isShowNextButton',
                'typeCounter',
                'maxTypes',
                'isStartDisable',
                'isStopDisable',
                'isDisableNextButton',
                'showLoader',
                'messages',
                'processStatus'
            ]);

            return this;
        },

        bindEvents: function () {
            $(this.selectors.productRegeneration + ',' + this.selectors.categoriesRegeneration)
                .on('change', this.checkVisibleStartButton.bind(this));
        },

        checkVisibleStartButton: function () {
            if (this.getSelectValue(this.categoriesRegenerationSelect)
                || this.getSelectValue(this.productRegenerationSelect)) {
                return this.isStartDisable(false);
            }

            this.isStartDisable(true);
        },

        getSelectValue: function (element) {
            return Number(element.val());
        },

        nextStep: function () {
            var index = parseInt(this.indexedSteps[this.currentStep()]);

            if (index + 1 < this.steps.length) {
                this.currentStep(this.steps[index + 1].code);
            }
        },

        stepChanged: function () {
            var stepConfig = this.steps[this.indexedSteps[this.currentStep()]];

            if (!_.isUndefined(stepConfig.action) && _.isFunction(this[stepConfig.action])) {
                this[stepConfig.action]();
            }
        },

        isDoneStep: function (code) {
            return this.indexedSteps[this.currentStep()] > this.indexedSteps[code];
        },

        initRegeneration: function (isFirstStep) {
            if (isFirstStep) {
                this.typeIndex = 0;
                this.typeCounter(0);
            }

            this.prepareSteps();
            this.currentRegenerateType(this.regenerateTypes[this.typeIndex]);
            this.currentStep('start');
        },

        prepareSteps: function () {
            var isRegenerateProducts = Number($(this.selectors.productRegeneration).val()),
                isRegenerateCategories = Number($(this.selectors.categoriesRegeneration).val());

            this.maxTypes(isRegenerateCategories + isRegenerateProducts);

            if (isRegenerateCategories && isRegenerateProducts) {
                this.isShowNextButton(true);
            } else if (isRegenerateProducts) {
                this.typeIndex += 1;
            }
        },

        start: function () {
            var startData = {};

            startData[this.typeId] = this.currentRegenerateType().index;
            startData.form = $(this.selectors.form).serialize();
            startData.form_key = window.FORM_KEY;
            this.showLoader(true);
            this.typeCounter(this.typeCounter() + 1);
            this.isDisableNextButton(true);
            this.messages([]);
            this.openModal();

            $.ajax({
                url: this.startUrl,
                data: startData,
                type: 'POST',
                success: function (result) {
                    if (!_.isUndefined(result.process_identity)) {
                        this.processIdentity = result.process_identity;
                        this.nextStep();
                    } else if (!_.isUndefined(result.error)) {
                        this.messages([result.error]);
                        this.currentStep('result');
                    }
                }.bind(this)
            });
        },

        stop: function () {
            var deferred = $.Deferred();

            CancelAlertModal(deferred).showCancelAlert();
            $.when(deferred).done(function (cancelStatus) {
                if (cancelStatus === 'cancel-regeneration') {
                    this.isStopDisable(true);
                    this.processStatus($t('Please wait...'));
                    this._terminateProcess();
                }
            }.bind(this));
        },

        process: function () {
            this.getStatus().done(function (data) {
                if (data.messages !== undefined) {
                    this.messages(data.messages);
                } else if (!_.isUndefined(data.error)) {
                    this.messages([data.error]);
                } else {
                    this.messages([]);
                }

                if (data.status === 'running' || data.status === 'starting' || data.status === 'pending') {
                    setTimeout(this.process.bind(this), 1000);
                }

                if (data.status === 'success' || data.status === 'failed') {
                    this.nextStep();
                }
            }.bind(this));
        },

        finish: function () {
            this.showLoader(false);

            if (this.currentRegenerateType().index === 1) {
                this.nextType();
            } else {
                this.isShowNextButton(false);
            }
        },

        nextType: function () {
            this.typeIndex += 1;
            this.isDisableNextButton(false);
        },

        getStatus: function () {
            var result = $.Deferred();

            $.get(this.statusUrl, { 'processIdentity': this.processIdentity }, function (data) {
                result.resolve(data);
            });

            return result;
        },

        closeModal: function () {
            var deferred = $.Deferred(),
                _super = this._super;

            if (this.currentStep() === 'result') {
                return this._super();
            }

            CancelAlertModal(deferred).showCancelAlert();
            $.when(deferred).done(function (cancelStatus) {
                if (cancelStatus === 'cancel-regeneration') {
                    this._terminateProcess();
                    _super.call(this);
                }
            }.bind(this));
        },

        _terminateProcess: function () {
            $.get(this.terminateUrl, { 'processIdentity': this.processIdentity }, function () {
                location.reload();
            });
        }
    });
});
