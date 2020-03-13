/**
 * @author Israel Yasis
 */
if (!window.MDirector) {
    var MDirector = {};
}
require([
    'prototype'
],function(prototype){
    MDirector.track = function(pattern) {
        var re = new RegExp(pattern);
        var url = window.location.href;
        var params = url.toQueryParams();

        if (re.test(url) && params.dataId) {
            Mage.Cookies.set('mdirector', params.dataId);
        }
    };
});

