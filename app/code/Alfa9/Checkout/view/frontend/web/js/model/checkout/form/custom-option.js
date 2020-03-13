/**
 * @author Israel Yasis
 */
define([
    'ko'
], function (ko) {
    'use strict';

    return {
        isLoading: ko.observable(false),
        updateForm: ko.observable(false),
        formData: ko.observableArray([]),
        error: ko.observable(false),
        errorMessage: ko.observable('')
    };
});
