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
                var columnVal = record.status,
                    label = '';

                if (!columnVal) {
                    return this._super();
                }
                switch (columnVal) {
                    case 'new':
                    case 'pending':
                        label = '<span class="grid-severity-notice" style="background:#fffbbb; color:#f38a5e; border-color: #f38a5e"><span>' + $t('Pending') + '</span></span>';
                        break;
                    case 'notapproved':
                        label = '<span  class="grid-severity-minor"><span>' + $t('Not Approved') + '</span></span>';
                        break;
                    case 'approved':
                        label = '<span class="grid-severity-notice"><span>' + $t('Approved') + '</span></span>';
                        break;
                    case 'retail_upgrade':
                        label = '<span class="grid-severity-notice" style="background:#fffbbb; color:#f38a5e; border-color: #f38a5e"><span>' + $t('Retail Upgrade') + '</span></span>';
                        break;
                    case 'unregistered':
                        label = '<span  class="grid-severity-minor"><span>' + $t('Unregistered') + '</span></span>';
                        break;
                }
                return label;
            }
        });
    }
);

