define([
    'jquery',
    'Amasty_PromoBanners/js/injector',
    'catalogAddToCart'
], function ($) {
    'use strict';
    var options = {
            context: []
        },
        banner = {
            data: {},
            selectors: {
                BANNER_SELECTOR: '.ambanners.ambanner-'
            },

            init: function () {
                var sectionIds = this.collectSectionIds();
                var bannerIds = this.collectBannerIds();
                this.getFromServer(sectionIds, bannerIds).done(function (responseData) {
                    this.data = responseData;
                    this.insertBanners();
                    this.injectBanners();
                    $("[data-role=tocart-form]").catalogAddToCart({});
                }.bind(this));
            },
            collectSectionIds: function () {
                var sections = [];

                $('[data-role="amasty-banner-container"]').each(function () {
                    sections.push($(this).data('position'));
                });

                return sections;
            },
            collectBannerIds: function () {
                var banners = [];

                $('[data-role="amasty-banner-container"]').each(function () {
                    banners.push($(this).data('bannerid'));
                });

                return banners;
            },
            getFromServer: function (sectionIds, bannerIds) {
                var parameters = {
                    sections: sectionIds,
                    banners: bannerIds,
                    context: options.context
                };

                return $.getJSON(options.dataUrl, parameters);
            },
            getSectionBanners: function (section) {
                if (!(section in this.data.sections)) {
                    return [];
                }

                return this.data.sections[section].map(function (id) {

                    return this.data.content[id];
                }.bind(this));
            },
            getBanners: function (bannerId) {
                if (this.data.banners.indexOf(bannerId) === -1) {
                    return [];
                }
                var insertedBanner = this.data.banners.find(function (element) {
                    return element === bannerId;
                }.bind(this));

                return this.data.content[insertedBanner];
            },
            insertBanners: function () {
                var self = this;

                $('[data-role="amasty-banner-container"]').each(function () {
                    var sectionId = $(this).data('position');

                    if (typeof sectionId === "number") {
                        $(this).html(self.getSectionBanners(sectionId).join(''));
                        if (sectionId === 15) {
                            $('.product-item-inner').append($(self.selectors.BANNER_SELECTOR + sectionId));
                            $(self.selectors.BANNER_SELECTOR + sectionId).show();
                        }
                    } else {
                        var bannerId = $(this).data('bannerid');
                        $(this).html(self.getBanners(bannerId));
                    }

                    self.addProductSidebarClass();
                });
            },

            addProductSidebarClass: function () {
                var sidebarPositions = [1, 2];
                $.each(sidebarPositions, function (index, value) {
                    var positionSelector = '[data-position="' + value + '"]';
                    $(positionSelector).find('li, a.product.photo.product-item-photo, .product.details.product-item-details.product-item-details').addClass('side-banner');
                });
            },

            injectBanners: function () {
                var container = $('[data-role="amasty-banner-container"][data-position='
                    + options.injectorSectionId + ']');

                if (container.length == 0) {
                    return;
                }

                Object.keys(this.data.injectorParams.banners).each(function (id) {
                    var params = this.data.injectorParams.banners[id];

                    var injectorInstance = new AmastyBannersInjector(
                        this.data.injectorParams.containerSelector,
                        this.data.injectorParams.itemSelector,
                        params.afterProductRow,
                        params.afterProductNum,
                        params.width
                    );

                    injectorInstance.inject(container.find('[data-banner-id=' + id + ']')[0]);
                }.bind(this));
            },

            'Amasty_PromoBanners/js/loader': function (settings) {
                options = $.extend(options, settings);
                banner.init();
            }
        };

    return banner;
});
