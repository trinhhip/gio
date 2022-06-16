require([
    'jquery',
    'Magento_Customer/js/model/customer',
    'domReady!'
], function($, customer) {
    var checkInputValues = setInterval(function () {
        var inputs = $('.checkout-index-index .input-text');
        var firstname = $('.checkout-index-index .fieldset input[name="firstname"].input-text');

        if (inputs.length >= 8 ) {
            var isAllFieldLoaded =  customer.isLoggedIn() ?$(firstname[0]).val() : $(firstname[0]).is(":visible");
            if(isAllFieldLoaded){
                inputs.each(function() {
                    $(this).removeAttr('placeholder');
                    if ($(this).val() != '') {
                        $(this).parents('.field').addClass('active');
                    }
                })

                $('.checkout-index-index')
                    .on('focus', '.input-text', function() {
                        $(this).parents('.field').addClass('active');
                    })
                    .on('blur', '.input-text', function() {
                        if ($(this).val() == '') {
                            $(this).parents('.field').removeClass('active');
                        }
                    })
                clearInterval(checkInputValues);
            }

        }
    }, 200)
})
