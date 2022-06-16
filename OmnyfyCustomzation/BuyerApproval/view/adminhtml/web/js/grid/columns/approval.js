define([
        'Magento_Ui/js/grid/columns/select',
        'mage/translate'
    ], function (Column, $t) {
        'use strict';

        return Column.extend({
            defaults: {
                bodyTmpl: 'ui/grid/cells/html'
            },
            getLabel: function (record) {
                if (!record.is_approved) { //not sure why it doesn't exist
                    return '';
                }

                var columnVal = record.is_approved[0];

                if (columnVal === 'pending') {
                    return '<span class="grid-severity-notice" style="background:#fffbbb; color:#f38a5e; border-color: #f38a5e"><span>' + $t('Pending') + '</span></span>';
                } else if (columnVal === 'notapproved') {
                    return '<span  class="grid-severity-minor"><span>' + $t('Not Approved') + '</span></span>';
                } else if (columnVal === 'approved') {
                    return '<span class="grid-severity-notice"><span>' + $t('Approved') + '</span></span>';
                }

                return '';
            }
        });
    }
);

