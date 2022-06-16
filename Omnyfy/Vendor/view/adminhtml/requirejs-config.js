var config = {
    map: {
        '*': {
            'Magento_ProductVideo/js/get-video-information':
                'Omnyfy_Vendor/js/get-video-information',
            'Magento_Sales/order/create/scripts':
                'Omnyfy_Vendor/order/create/scripts',
            'Magento_Ui/js/form/element/file-uploader':
                'Omnyfy_Vendor/js/form/element/file-uploader',
            reloadGrid: 'Omnyfy_Vendor/js/componentReloader'
        }
    },
    config: {
        mixins: {
            'Magento_Swatches/js/product-attributes': {
                'Omnyfy_Vendor/js/product-attributes': true
            }
        }
    }
};
