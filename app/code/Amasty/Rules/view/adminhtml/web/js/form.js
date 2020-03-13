define([
    'jquery',
    'uiRegistry',
    'mage/translate',
], function ($, registry) {
    var defaultOptions;
    var amrulesForm = {
        update: function (type) {
            if (!defaultOptions) {
                defaultOptions = [];
                options = $('[data-index="amrulesrule[apply_discount_to]"] select')[0].options;
                $.each(options, function(key, el) {
                    defaultOptions[key] = el.label;
                });
            }
            $.each($('[data-index="amrulesrule[apply_discount_to]"] select')[0].options, function(key, el){
                el.label = defaultOptions[key];
                el.text = defaultOptions[key];
            });
            var action = '';
            this.resetFields(type);
            this.renameFieldsToBaseNames();
            var actionFieldset = $('#' + type +'rule_actions_fieldset_').parent();
            var notice = $('[data-index="simple_action"] .admin__field-note');
            var discountStep = $('[data-index="discount_step"]');
            var promoCategories = $('[data-index="amrulesrule[promo_cats]"]');
            var discountQty = $('[data-index="discount_qty"]');
            var applyTo = require('uiRegistry').get("sales_rule_form.sales_rule_form.actions.actions_apply_to.html_content");

            window.amRulesHide = 0;

            actionFieldset.show();
            if (typeof window.amPromoHide !="undefined" && window.amPromoHide == 1) {
                actionFieldset.hide();
            }

            var selector = $('[data-index="simple_action"] select');
            if (type !== 'sales_rule_form') {
                action = selector[1] ? selector[1].value : selector[0].value;
            } else {
                action = selector.val();
            }

            this.checkFieldsValue();
            this.renameRulesSetting(action);
            this.changeNoticeValue(notice, action);
            this.showElement(discountStep);
            this.showElement(discountQty);
            applyTo.visible(true);
            this.showElement(notice);

            switch (action) {
                case 'groupn':
                    this.hideElement(discountQty);
                    this.showElement(notice);
                    this.showFields(['amrulesrule[skip_rule]','amrulesrule[apply_discount_to]', 'amrulesrule[max_discount]'], type);
                    break;
                case 'thecheapest':
                case 'themostexpencive':
                case 'moneyamount':
                    this.showFields(['amrulesrule[skip_rule]', 'amrulesrule[priceselector]', 'amrulesrule[max_discount]'], type);
                    break;
                case 'aftern_disc':
                    this.renameDropdownOptions();
                    this.showFields(['amrulesrule[skip_rule]','amrulesrule[apply_discount_to]', 'amrulesrule[priceselector]', 'amrulesrule[max_discount]'], type);
                    break;
                case 'eachn_perc':
                    this.showFields(['amrulesrule[use_for]', 'amrulesrule[skip_rule]', 'amrulesrule[apply_discount_to]', 'amrulesrule[priceselector]', 'amrulesrule[max_discount]'], type);
                    break;
                case 'groupn_disc':
                    this.showFields(['amrulesrule[skip_rule]','amrulesrule[apply_discount_to]', 'amrulesrule[priceselector]', 'amrulesrule[max_discount]'], type);
                    break;
                case 'eachn_fixdisc':
                case 'eachn_fixprice':
                    this.checkPriceSelector();
                    this.showFields(['amrulesrule[use_for]', 'amrulesrule[skip_rule]','amrulesrule[apply_discount_to]', 'amrulesrule[max_discount]'], type);
                    break;
                case 'aftern_fixdisc':
                case 'aftern_fixed':
                    this.checkPriceSelector();
                    this.showFields(['amrulesrule[skip_rule]','amrulesrule[apply_discount_to]', 'amrulesrule[max_discount]'], type);
                    break;
                case 'eachmaftn_perc':
                    this.renameDropdownOptions();
                    this.showFields(['amrulesrule[eachm]','amrulesrule[apply_discount_to]', 'amrulesrule[skip_rule]', 'amrulesrule[priceselector]', 'amrulesrule[max_discount]'], type);
                    break;
                case 'eachmaftn_fixdisc':
                case 'eachmaftn_fixprice':
                    this.renameDropdownOptions();
                    this.checkPriceSelector();
                    this.showFields(['amrulesrule[eachm]','amrulesrule[apply_discount_to]', 'amrulesrule[skip_rule]', 'amrulesrule[max_discount]'], type);
                    break;
                case 'buyxgetn_fixprice':
                case 'buyxgetn_fixdisc':
                    this.checkPriceSelector();
                    this.showFields(['amrulesrule[nqty]', 'amrulesrule[skip_rule]', 'amrulesrule[max_discount]'], type);
                    this.showPromoItems();
                    this.showNote();
                    break;
                case 'buyxgetn_perc':
                    this.showFields(['amrulesrule[nqty]', 'amrulesrule[skip_rule]', 'amrulesrule[priceselector]', 'amrulesrule[max_discount]'], type);
                    this.showPromoItems();
                    this.showNote();
                    break;
                case 'setof_fixed':
                    this.hideElement(discountStep);
                case 'setof_percent':
                    applyTo.visible(false);
                    actionFieldset.hide();
                    window.amRulesHide = 1;
                    this.showFields(['amrulesrule[max_discount]'], type);
                    this.showPromoItems();
                    this.hideElement(promoCategories);
                    break;
                default:
                    this.hideElement(notice);
            }

            if (action.indexOf('fixprice') >= 0 || action == 'groupn' || action == 'setof_fixed'){
                this.hideFields(['apply_to_shipping'],type);
            }
        },

        renameDropdownOptions: function () {
            var newOptions = {
                    'asc' : $.mage.__('Cheapest products, considering rule logic'),
                    'desc' : $.mage.__('Most expensive products, considering rule logic')
                },
                dropdown = $('[data-index="amrulesrule[apply_discount_to]"] select')[0].options;
            $.each(dropdown, function(key, el) {
                el.label = newOptions[ el.value ];
                el.text = newOptions[ el.value ];
            });
        },

        checkPriceSelector: function () {
            var priceselector = require('uiRegistry').get('sales_rule_form.sales_rule_form.actions.amrulesrule[priceselector]');

            if(priceselector.value() != 0) {
                priceselector.value(0);
            }
        },

        showPromoItems: function () {
            $('[data-index="promo_items"]').show();
        },

        hidePromoItems: function () {
            $('[data-index="promo_items"], [data-index="discount_step"] .admin__field-note').hide();
        },

        showNote: function () {
            $('[data-index="discount_step"] .admin__field-note').show();
        },

        resetFields: function (type) {
            this.showFields([
                'discount_qty', 'discount_step', 'apply_to_shipping', 'simple_free_shipping'
            ], type);
            this.hideFields([
                'amrulesrule[skip_rule]',
                'amrulesrule[max_discount]',
                'amrulesrule[nqty]',
                'amrulesrule[priceselector]',
                'amrulesrule[eachm]',
                'amrulesrule[apply_discount_to]',
                'amrulesrule[use_for]'
            ], type);
            this.hidePromoItems();
        },

        hideFields: function (names, type) {
            return this.toggleFields('hide', names, type);
        },

        showFields: function (names, type) {
            return this.toggleFields('show', names, type);
        },

        addPrefix: function (names, type) {
            for (var i = 0; i < names.length; i++) {
                names[i] = type + '.' + type + '.' + 'actions.' + names[i];
            }

            return names;
        },

        toggleFields: function (method, names, type) {
            registry.get(this.addPrefix(names, type), function () {
                for (var i = 0; i < arguments.length; i++) {
                    arguments[i][method]();
                }
            });
        },

        renameRulesSetting: function (action) {
            var discountStep = $('[data-index="discount_step"] label span'),
                discountAmount = $('[data-index="discount_amount"] label span'),
                discountQty = $('[data-index="discount_qty"] label span');

            switch (action) {
                case 'buy_x_get_y':
                    discountStep.text($.mage.__("Buy N Products"));
                    discountAmount.text($.mage.__("Number of Products with Discount"));
                    break;
                case 'eachn_perc':
                case 'eachn_fixdisc':
                case 'eachn_fixprice':
                    discountStep.text($.mage.__("Each N-th"));
                    break;
                case 'eachmaftn_perc':
                case 'eachmaftn_fixdisc':
                case 'eachmaftn_fixprice':
                    discountStep.text($.mage.__("Each Product (step)"));
                    break;
                case 'buyxgetn_perc':
                case 'buyxgetn_fixprice':
                case 'buyxgetn_fixdisc':
                    discountStep.text($.mage.__("Number of X Products"));
                    break;
                case 'setof_fixed':
                    discountQty.text($.mage.__("Max Number of Sets Discount is Applied To"));
                    break;
                default:
                    discountStep.text($.mage.__("Discount Qty Step (Buy X)"));
                    discountAmount.text($.mage.__("Discount Amount"));
                    break;
            }
        },

        showElement: function (name) {
            name.show();
        },

        hideElement: function (name) {
            name.hide();
        },

        renameFieldsToBaseNames:function ()
        {
            var discountQty = $('[data-index="discount_qty"] label span');
            discountQty.text($.mage.__("Maximum Qty Discount is Applied To"));
        },

        checkFieldsValue:function () {
            var discountQty = require('uiRegistry').get('sales_rule_form.sales_rule_form.actions.discount_qty'),
                discountStep = require('uiRegistry').get('sales_rule_form.sales_rule_form.actions.discount_step');

            if(discountQty.value() < 0) {
                discountQty.value(0);
            }

            if(discountStep.value() == 0 || discountStep.value() == '') {
                discountStep.value(1);
            }
        },

        changeNoticeValue: function(notice, action) {
            var groupnNoticeText = $.mage.__('Please, change the priority of this rule to 0. If more than one rule has priority 0, the discount can be calculated incorrectly </br>'),
                noticeContent = '';

            switch (action) {
                case 'thecheapest':
                    noticeContent = this.formUserGuideLink('1#the_cheapest_also_for_buy_1_get_1_free');
                    break;
                case 'themostexpencive':
                    noticeContent = this.formUserGuideLink('2#the_most_expensive');
                    break;
                case 'moneyamount':
                    noticeContent = this.formUserGuideLink('3#get_y_for_each_x_spent');
                    break;
                case 'buyxgetn_perc':
                case 'buyxgetn_fixprice':
                case 'buyxgetn_fixdisc':
                    noticeContent = this.formUserGuideLink('4#buy_x_get_y_x_and_y_are_different_products');
                    break;
                case 'eachn_perc':
                case 'eachn_fixdisc':
                case 'eachn_fixprice':
                    noticeContent = this.formUserGuideLink('5#each_n-th');
                    break;
                case 'eachmaftn_perc':
                case 'eachmaftn_fixdisc':
                case 'eachmaftn_fixprice':
                    noticeContent = this.formUserGuideLink('6#each_product_after_n');
                    break;
                case 'setof_fixed':
                case 'setof_percent':
                    noticeContent = this.formUserGuideLink('8#product_set');
                    break;
                case 'groupn_disc':
                    noticeContent = this.formUserGuideLink('7#each_group_of_n');
                    break;
                case 'groupn':
                    noticeContent = groupnNoticeText + this.formUserGuideLink('7#each_group_of_n');
                    break;
            }
            $(notice).html(noticeContent);
        },

        formUserGuideLink: function (ruleIdentificator) {
            return $.mage.__('Please see ')
                + '<a href="https://amasty.com/docs/doku.php?id=magento_2%3Aspecial-promotions&utm_source=extension&utm_medium=link&utm_campaign=userguide_sp_m2_'
                + ruleIdentificator
                + '">'
                + $.mage.__('usage example') + '</a>';
        }
    };

    return amrulesForm;
});