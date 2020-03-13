/**
 * @author Israel Yasis
 */
define([
    'mage/storage',
    'Alfa9_Checkout/js/model/checkout/form/custom-option',
    'mage/translate'
], function (storage, customFormModel, $t) {
    'use strict';
    return function (dataCustomForm) {
        return storage.post(
            'alfa9checkout/ajax/updatecustomcheckoutform',
            JSON.stringify(dataCustomForm),
            false
        ).done(function (response) {
            if (response && response.errors === false) {
                Object.keys(dataCustomForm).forEach(function(key){
                    customFormModel.formData.push({ label: key, value: dataCustomForm[key]})
                });
                customFormModel.updateForm(true);
            } else {
                dataCustomForm.error(true);
                dataCustomForm.errorMessage($t('Error while updating the values'));
            }
        }).fail(function () {
            customFormModel.error(true);
            customFormModel.errorMessage($t('Error while updating the values'));
        }).always(function (){
            customFormModel.isLoading(false);
        });
    };
});
