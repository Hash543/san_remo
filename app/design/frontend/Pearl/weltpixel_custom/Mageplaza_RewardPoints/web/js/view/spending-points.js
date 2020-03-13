/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPoints
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */
define([
    'jquery',
    'ko',
    'underscore',
    'uiComponent',
    'Mageplaza_RewardPoints/js/action/update-spent-points',
    'Magento_Customer/js/customer-data',
    'Mageplaza_RewardPoints/js/model/points',
    'mage/translate',
    'mpIonRangeSlider'
], function ($, ko, _, Component, spendingPoints, customerData, points, $t) {
    'use strict';

    var spendingConfig = points.spendingConfig;
    var self;
    var pauseSubscribe = false;
    return Component.extend({
        defaults: {
            template: 'Mageplaza_RewardPoints/spending-points'
        },
        isCheckoutCart: points.isCheckoutCart,
        rules: ko.observableArray(),
        selectedRule: ko.observable(),
        pointSpent: ko.observable(),
        canVisibleSpendPoints: ko.observable(true),
        isDisplaySlider: ko.observable(true),
        useMaxPoint: ko.observable(true),
        pauseSubscribe: ko.observable(false),
        slider: null,
        oldValue: null,
        isChangeRule: false,
        balanceFormatted: ko.computed(function () {
            var label = $t('You have %s');
            return label.replace('%s', '<strong>' + points.format(points.balance()) + '</strong>');
        }),

        /**
         * Initialize
         */
        initialize: function () {
            this._super();
            self = this;
            this.initData(spendingConfig());
            this.canSpendPoints = ko.computed(function () {
                return !$.isEmptyObject(spendingConfig()) &&  this.rules() && this.rules().length && this.selectedRule();
            }, this);
            if (this.selectedRule()) {
                this.isDisplaySlider(this.selectedRule().isDisplaySlider);
            }

        },

        /**
         * @param value
         */
        initData: function (value) {
            this.rules(value.rules);
            this.initSelectedRule(value.ruleApplied);
            var slidePoint = value.pointSpent;
            if (this.selectedRule() &&
                Number(slidePoint) > Number(this.selectedRule().max) &&
                this.selectedRule().isDisplaySlider
            ) {
                slidePoint = this.selectedRule().max;
            }
            this.pointSpent(slidePoint);

        },

        /**
         * Init observer event
         * @return {exports}
         */
        initObservable: function () {
            this._super();

            var self = this;

            spendingConfig.subscribe(function (value) {
                if (value.length === 0 && self.slider) {
                    if (!pauseSubscribe) {
                        spendingPoints(0, 'no_apply');
                        pauseSubscribe = true;
                    }
                } else {
                    if (!_.isEqual(value.rules, self.rules())) {
                        self.initData(value);
                        if (self.slider) {
                            self.updateSlider();
                        }
                    }
                }
            });

            return this;
        },

        changePointSpent: function (obj, event) {
            if (event && event.originalEvent) {
                var rule = this.selectedRule();
                var value = parseInt(this.pointSpent());
                var newValue = (value < rule.min) ? rule.min : ((value > rule.max) ? rule.max : value);
                if (newValue !== this.slider.old_from) {
                    this.updateValueOnSlider(newValue);
                }
            }
        },
        changeMaxPoint: function (obj, event) {
            if (event && event.originalEvent) {
                var newValue = this.useMaxPoint() ? self.selectedRule().max : self.selectedRule().min;
                this.updateValueOnSlider(newValue);
            }
        },

        updateValueOnSlider: function (newValue) {
            self.pointSpent(newValue);
            this.slider.update({from: newValue});
        },

        /**
         * @param obj
         * @param event
         */
        changeRule: function (obj, event) {
            if (event && event.originalEvent) {
                var rule = this.selectedRule();
                if (!rule) {
                    return;
                }

                if (this.slider) {
                    this.pointSpent(0);
                    this.isDisplaySlider(rule.isDisplaySlider);
                    this.slider.update({
                        min: rule.min,
                        max: rule.max,
                        from: 0
                    });
                    this.isChangeRule = true;
                }
            }
        },

        /**
         * @param ruleId
         * @return {exports}
         */
        initSelectedRule: function (ruleId) {
            var selectedRule;
            if (this.rules() && this.rules().length) {
                if (ruleId) {
                    $.each(this.rules(), function (index, rule) {
                        if (rule.id === ruleId) {
                            selectedRule = rule;
                            return false;
                        }
                    });
                }
                if (!selectedRule) {
                    selectedRule = this.rules()[0];
                }

                if (!_.isEqual(selectedRule, this.selectedRule())) {
                    this.selectedRule(selectedRule);
                }
            } else {
                this.selectedRule(null);
            }

            return this;
        },

        /**
         * Init spend slider
         */
        initSlider: function () {
            var self = this,
                range = $(".reward-range-slider"),
                rangeFinishFirstTime = true;

            range.ionRangeSlider({
                type: "single",
                min: 0,
                max: 0,
                from: 0,
                step: 0,
                onChange: function (data) {
                    self.pointSpent(data.from);
                },
                onFinish: function () {
                    if (rangeFinishFirstTime) {
                        return;
                    }
                    self.checkMaxPointByAction();
                    self.sendUpdateSpentPoints();
                },
                onUpdate: function () {
                    if (rangeFinishFirstTime) {
                        return;
                    }
                    self.checkMaxPointByAction();
                    self.sendUpdateSpentPoints();

                }
            });
            this.slider = range.data("ionRangeSlider");
            self.updateSlider();
            var isUseMaxPointByDefault = (this.pointSpent() === null && spendingConfig().useMaxPoints);
            var isUseMaxPointByAction = this.pointSpent() == this.selectedRule().max;
            if (isUseMaxPointByDefault) {
                this.pointSpent(this.selectedRule().max);
                this.updateSlider();
            }
            this.useMaxPoint(isUseMaxPointByDefault || isUseMaxPointByAction);
            rangeFinishFirstTime = false;

            // if (!window.checkoutConfig.selectedShippingMethod) {
            this.sendUpdateSpentPoints()
            // }
        },
        updateSlider: function () {
            var rule = this.selectedRule();
            if (rule) {
                this.slider.update({
                    min: rule.min,
                    max: rule.max,
                    step: rule.step,
                    from: self.pointSpent()
                });
            }
        },
        sendUpdateSpentPoints: function () {
            if (this.pointSpent() !== this.oldValue || this.isChangeRule) {
                this.updateSpendPoints();
                this.oldValue = this.pointSpent();
            }
        },

        checkMaxPointByAction: function () {
            this.useMaxPoint(this.pointSpent() == this.selectedRule().max);
        },

        /**
         * Send ajax spend point to sv
         */
        updateSpendPoints: function () {
            if (isNaN(this.pointSpent())) {
                this.pointSpent(0);
            }
            spendingPoints(this.pointSpent(), this.selectedRule().id);
        },

        /**
         * Get mage init
         * @returns {*}
         */
        getCollapsible: function () {
            return this.isCheckoutCart ? '' : {'collapsible': {'openedState': '_active'}};
        }
    });
});