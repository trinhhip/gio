define(['jquery'],function ($){
    return function(){
        const LIMIT_COUNTRY_NUMBER = 5;
        $(document).ready(function() {

            var last_valid_selection = null;

            $('.limit-select').change(function(event) {

                if ($(this).val().length > LIMIT_COUNTRY_NUMBER) {

                    $(this).val(last_valid_selection);
                } else {
                    last_valid_selection = $(this).val();
                }
            });
        });
    }
})

