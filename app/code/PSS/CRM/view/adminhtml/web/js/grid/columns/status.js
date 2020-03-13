/*
 * @copyright (c) 2018. Alfa9 (http://www.alfa9.com)
 * @author Xavier Sanz <xsanz@alfa9.com>
 * @package pss_crm
 */

define([
    'Magento_Ui/js/grid/columns/select'
], function (Column) {
    'use strict';


     return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/html'
        },
        getLabel: function (record) {
            var response = '';
            var label = this._super(record);
            switch (record.process_status) {
                    case '1': //OK
                        response = '<span class="grid-severity-notice"><span>' + label + '</span></span>';
                        break;
                    case '2': //ERROR
                        response = '<span class="grid-severity-critical"><span>' + label + '</span></span>';
                        break;
                    case '0': //PENDING
                        response = '<span class="grid-severity-minor"><span>' + label + '</span></span>';
                        break;
            }
            return response;
        }
    });
});

