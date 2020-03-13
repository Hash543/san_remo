define(["jquery", 'mage/cookies'], function ($) {
    var globalPromoComponent = function(config, node)
    {
        $(document).ready(function() {
            function showGlobalNotification() {
                var headerSection = $('.page-wrapper div.page-header');
                if (!headerSection.hasClass('sticky-header')) {
                    $(node).show();
                }
            }

            if (!$.cookie('weltpixel_global_notification')  ) {
                showGlobalNotification();
            }

            $(node).find('.close-global-notification').bind('click', function() {
                $.cookie('weltpixel_global_notification', true);
                $(node).hide();
            });
        });
    };

    return globalPromoComponent;
});