define(['jquery', 'jquery/ui', 'mage/translate', 'domReady'], function ($) {
    "use strict";

    var navigationJs =
        {
            init: function() {
                var navigation = $('.navigation');
                navigationJs.adjustLevelTopFullwidth(navigation);

                navigation.find('.level0.submenu').on('mouseenter', function() {
                    navigationJs.updateBold($(this));
                });
                if (!navigationJs.isCheckoutPage()) {
                    var scroll = 15;
                    if(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
                        scroll = 0;
                    }

                    var ww = $(window).width() + scroll;
                    if (ww <= parseInt(window.widthThreshold)) {
                        $('.navigation').show();
                    }

                    var searchBlock = $('.page-header-v2 .block-search').not('.minisearch-v2'),
                        languageBlock = $('#switcher-language');
                    if (ww >= window.screenM && ww <= parseInt(window.widthThreshold)) {
                        $('body').addClass('mobile-nav');
                        if (languageBlock.length) languageBlock.show();
                        if (searchBlock.length) searchBlock.css({'right': $('.header_right').outerWidth() + 'px'});
                    } else {
                        $('body').removeClass('mobile-nav');
                        if (languageBlock.length && $('.nav-toggle').is(':visible')) languageBlock.hide();
                        if (searchBlock.length) searchBlock.css({'right': ''});
                    }
                }

                $('.action.nav-toggle').on('click', function() {
                    var is_safari =  navigator.userAgent.indexOf('Safari') != -1 && navigator.userAgent.indexOf('Chrome') == -1 &&  navigator.userAgent.indexOf('Android') == -1
                    if (is_safari) {
                        if ($('html').hasClass('nav-open')) {
                            $('.page-wrapper').css('overflow','hidden');
                        } else {
                            $('.page-wrapper').css('overflow','visible');
                        }
                    }
                });
            },
            isCheckoutPage: function() {
                return $('body').hasClass('checkout-index-index');
            },
            adjustLevelTopFullwidth: function() {
                var pageWrapperW = $('.page-wrapper').width(),
                    headerContentW = $('.header.content').outerWidth(),
                    fullwidthWrapper = $('.navigation').find('.fullwidth-wrapper');

                fullwidthWrapper.each(function() {
                    $(this)
                        .css({'width': pageWrapperW + 'px'})
                        .find('.fullwidth-wrapper-inner')
                        .css({'width': headerContentW + 'px'});
                });
            },
            updateBold: function(el) {
                var parent = el.closest('.megamenu').find('a.level-top');
                parent.addClass('bold-menu');
                el.on('mouseleave', function() {
                    parent.removeClass('bold-menu');
                });
            }
        };

    return navigationJs;
});