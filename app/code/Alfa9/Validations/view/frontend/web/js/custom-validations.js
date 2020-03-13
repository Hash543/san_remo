/**
 * @author Israel Yasis
 */
define([
    'jquery'
], function ($) {
    'use strict';
    return function() {
        $.validator.addMethod(
            'validate-nif-nie',
            function (v) {
                var validChars = 'TRWAGMYFPDXBNJZSQVHLCKET';
                var nifRegExp = /^[0-9]{8}[TRWAGMYFPDXBNJZSQVHLCKET]{1}$/i;
                var nieRegExp = /^[XYZ]{1}[0-9]{7}[TRWAGMYFPDXBNJZSQVHLCKET]{1}$/i;
                var str = v.toString().toUpperCase();
                if (!nifRegExp.test(str) && !nieRegExp.test(str)) {
                    return false
                }

                var nie = str
                    .replace(/^[X]/, '0')
                    .replace(/^[Y]/, '1')
                    .replace(/^[Z]/, '2');
                var letter = str.substr(-1);
                var charIndex = parseInt(nie.substr(0, 8)) % 23;
                if (validChars.charAt(charIndex) === letter) {
                    return true;
                }
                return false;
            },
            $.mage.__('The NIF/NIE is invalid.')
        );
    }
});