define([
    "jquery",
    "jquery/colorpicker/js/colorpicker"
], function ($) {
    'use strict';
    return function (config) {
        var $el = $("#"+config.htmlId);
        $el.css("backgroundColor", config.elData);

        $el.ColorPicker({
            color: config.elData,
            onChange: function (hsb, hex, rgb) {
                $el.css("backgroundColor", "#" + hex).val("#" + hex);
            }
        });
    };
});