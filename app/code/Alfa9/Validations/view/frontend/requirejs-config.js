/**
 * @author Israel Yasis
 */
const config = {
    config : {
        mixins : {
            'mage/validation': {
                'Alfa9_Validations/js/custom-validations': true
            },
            'Magento_Ui/js/lib/validation/rules': {
                'Alfa9_Validations/js/custom-validations-rules': true
            }
        }
    }
};
