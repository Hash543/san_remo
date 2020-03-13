define([
    "jquery",
    'mage/url',
    "mage/cookies"
], function($, urlBuilder) {

    return function () {
        $('#amgdprcookie-btn-save').click(function (e) {
            e.preventDefault();
            var url = urlBuilder.build('gdprcookie/cookie/savegroups'),
                form = $('#amgdprcookie-form')

            $.ajax({
                showLoader: true,
                method: "POST",
                url: url,
                data: form.serialize()
            }).done(function () {
                $("#gdpr-cookie-block").remove();
            });
        });

        $('#amgdprcookie-btn-all').click(function (e) {
            e.preventDefault();
            var url = urlBuilder.build('gdprcookie/cookie/allow')

            $.ajax({
                method: "POST",
                url: url,
                async: false
            }).done(function () {
                location.reload();
            });
        });
    }
});