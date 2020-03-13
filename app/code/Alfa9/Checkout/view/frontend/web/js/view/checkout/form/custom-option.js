/**
 * @author Israel Yasis
 */
define([
    'Magento_Ui/js/form/form',
    'Alfa9_Checkout/js/action/update-custom-form',
    'Alfa9_Checkout/js/model/checkout/form/custom-option',
], function(Component, updateCustomForm, customFormModel){
    'use strict';
    return Component.extend({
        customFormModel: customFormModel,
        initialize: function(){
            this._super();
            return this;
        },
        onSubmit: function () {
            this.source.get('param.invalid', false);
            this.source.trigger('customCheckoutForm.data.validate');
            if(!this.source.get('param.invalid')) {
                customFormModel.formData([]);
                customFormModel.error(false);
                customFormModel.errorMessage('');
                customFormModel.isLoading(true);
                updateCustomForm(this.source.get('customCheckoutForm'));
            }
        },
        edit: function () {
            customFormModel.updateForm(false);
        }
    });
});