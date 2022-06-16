/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'Magento_Ui/js/grid/controls/columns'
], function (ColumnsControls) {
    'use strict';

    return ColumnsControls.extend({
        defaults: {
            template: 'Omnyfy_Mcm/grid/controls/columns',
        },
    });
});
