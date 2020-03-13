/**
 * @author Israel Yasis
 */
define([
        'jquery',
        'ko',
        'uiComponent',
        'PSS_SampleProducts/js/model/sample-products',
        //'PSS_SampleProducts/js/action/add-to-cart',
        //'PSS_SampleProducts/js/action/remove-to-cart',
        'owl_carousel',
        'owl_config'
    ],
    function ($, ko,  Component, sampleProducts) {
        'use strict';
        ko.bindingHandlers.owlCarousel = {
            init: function () {
                $('#owl-carousel-suggested').owlCarousel({
                    nav: true,
                    responsive: {
                        0    :{ items           :1},
                        600  :{ items           :2},
                        1000 :{ items           :3}
                    }
                });
                /*$('#owl-carousel-suggested .sample-products-checkbox').on('click change', function(event) {
                    event.stopPropagation();
                });*/
            }
        };
        return Component.extend({
            maxNumberProduct: 1,
            formKey: '',
            sampleProducts: sampleProducts,
            defaults: {

            },
            initialize: function (config) {
                this._super();
                var self = this;
                if(typeof config.maxNumberProduct !== 'undefined') {
                    this.maxNumberProduct = config.maxNumberProduct;
                }
                if(typeof config.formKey !== "undefined") {
                    this.formKey = config.formKey;
                }
                this.hideCheckboxes();
                $('body').on('change','.sample-products-checkbox', function() {
                    //const productId = this.value;
                    //const formKey = self.formKey;
                    //const numberChecked = $('.sample-products-checkbox:checked').length;
                    self.hideCheckboxes();

                    /*if($(this).is(":checked") && numberChecked <= self.maxNumberProduct) {
                        addToCart(productId, formKey);
                    } else if(!$(this).is(":checked")){
                        removeToCart(productId, formKey);
                    }*/
                });
            },
            hideCheckboxes: function () {
                const numberChecked = $('.sample-products-checkbox:checked').length;
                if(numberChecked >= this.maxNumberProduct) {
                    $('.sample-products-checkbox:not(:checked)').css('opacity', '0');
                } else {
                    $('.sample-products-checkbox:not(:checked)').css('opacity', '1');
                }
            }
        });
    }
);