define(
    [
        'jquery',
        'uiComponent',
        'underscore',
        'jquery/jquery.hashchange'
    ],
    function (
        $,
        Component,
        _
    ) {
        'use strict';


        return Component.extend({
            defaults: {
                template: 'PSS_AddStepToCheckout/nav-bar-step'
            },

            /**
             *
             * @returns {*}
             */
            initialize: function () {
                var self = this;
                this._super();
                this.updateClass();
            },

            /**
             *  when switching between steps
             */
            setCurrentClass: function () {
                var select = $(".arrow-steps .step");
                $(window).on('hashchange', function (e) {
                    if (window.location.hash === '#shipping') {
                        select.last().prev().addClass('current');
                        select.last().removeClass('current');
                        $("body.checkout-index-index").addClass('is_shipping');
                    } else {
                        select.last().addClass('current');
                        select.last().prev().removeClass('current');
                        $("body.checkout-index-index").removeClass('is_shipping');
                    }
                });
            },

            /**
             *  when the page is updated
             */
            updateClass: function () {
                var select = $(".arrow-steps .step");
                if (window.location.hash === '#shipping') {
                    select.last().prev().addClass('current');
                    $("body.checkout-index-index").addClass('is_shipping');
                } else if (window.location.hash === '#payment') {
                    select.last().addClass('current');
                    $("body.checkout-index-index").removeClass('is_shipping');
                } else {
                    $("body.checkout-index-index").removeClass('is_shipping');
                }
            }
        });
    }
);