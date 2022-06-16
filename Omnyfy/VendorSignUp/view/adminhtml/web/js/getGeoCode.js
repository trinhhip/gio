define([
    'jquery',
    'mage/storage'
], function($, mageStorage) {
    return function(config) {
        $('.get-geocode-btn').on('click', function (e){
            e.preventDefault();
            let fetchGeoCodeUrl = config.fetchGeoCodeUrl;
            let address = $('#vendor_business_address').val();
            fetchGeoCodeUrl += `address/${encodeURI(address)}/vendorId/${config.vendorId}`;
            $("body").trigger('processStart');
            mageStorage.post(
                fetchGeoCodeUrl
            ).done(
                function (response) {
                    let result = response ? JSON.parse(response) : '';
                    if(result.addressLat && result.addressLng){
                        $('#vendor_latitude').val(result.addressLat);
                        $('#vendor_longitude').val(result.addressLng);
                    } else{
                        alert(`No matching results for ${address}`);
                    }
                }
            ).fail(
                function (response) {
                    alert('Something went wrong. Please try again')
                }
            ).always(
                function (response){
                    $("body").trigger('processStop');
                }
            );
        })
    }
});