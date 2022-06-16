define([
    'jquery',
    'accordion',
    "mage/calendar",
    'mage/mage',
    'jquery/validate',
    'domReady!'
], function ($) {
    return function(config, element) {
        var formActionUrl = config.formActionUrl,
            ajaxUploadUrl = config.ajaxUploadUrl,
            ajaxEmailValidationUrl = config.ajaxEmailValidationUrl;

        var validateEmail = function (elementValue) {
            var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
            return emailPattern.test(elementValue);
        }

        var formSubmit = function () {
            var params = $('#vendor-signup-form').serialize();

            $.ajax({
                url: formActionUrl,
                type: "POST",
                dataType: "json",
                data: params,
                showLoader: true,
                cache: false,
                success: function (response) {
                    window.location.href = response.redirect;
                }
            });
        }

        $("#vendor-signup-form").each(function () {
            $(this, "input, textarea, select");
        });
        $('#vendor-signup-form input, select, textarea').on('keypress change',
            function (index) {
                var input = $(this);
                if (input.val()) {
                    //alert('Type: ' + input.attr('type') + 'Name: ' + input.attr('name') + 'Value: ' + input.val());
                    $('#' + input.attr('id')).removeClass('mage-error');
                    $('#' + input.attr('id') + '-error').hide();
                }
            }
        );

        $(".input-file").on("change", function () {
            var file = $(this).get(0).files[0];
            var formData = new FormData();

            formData.append('file', file);

            $.ajax({
                url: ajaxUploadUrl,
                cache: false,
                contentType: false,
                processData: false,
                type: 'POST',
                showLoader: true,
                data: formData,
                success: function (response) {
                    $('.file-upload-message').remove();
                    $(".input-file").after('<i class="file-upload-message" style="font-size: 12px">' + response["message"] + '</i>')
                    if (response.type == 'success') {
                        $("input[name='file-name']").val(response["filelist"]);
                    }
                },
                fail: function () {
                    alert('Something went wrong while uploading the file.')
                },
                always: function () {
                }
            });
        });

        $('#email').keyup(function () {
            var value = $(this).val();
            var valid = validateEmail(value);
            if (valid) {
                $.ajax({
                    url: ajaxEmailValidationUrl,
                    type: "POST",
                    dataType: "json",
                    data: {
                        email: value
                    },
                    showLoader: false,
                    cache: false,
                    success: function (response) {
                        if (response['type'] == 'exist') {
                            $('.email-section #email-error').text(response['message']);
                            $('.email-section #email-error').show();
                            $('#email').val('');
                        } else {
                            $("#email-error").text('');
                            $('.email-section #email-error').hide();
                        }
                    }
                });
            }
        });

        function isValidAbn(abn) {
            var weights = [10, 1, 3, 5, 7, 9, 11, 13, 15, 17, 19];
            abn = abn.replace(/\D/g, '');
            // Check abn is 11 chars long
            if (abn.length != 11) {
                return false;
            }
            // Sum the products
            var sum = 0;
            $.each(abn.split(''), function (index, value) {
                // Subtract one from first digit
                if (index == 0) {
                    value = (value * 1) - 1;
                }
                sum += (value * weights[index]);
            });
            if ((sum % 89) != 0) {
                return false;
            }
            return true;
        }

        var subscriptionChild = document.getElementById('subscription-child');
        var cardElement = document.getElementById('card-element');

        if (subscriptionChild && cardElement) {
            var apiKey = $('#stripe-api-key-242').html();
            var stripe = Stripe(apiKey);

            var elements = stripe.elements();

            var style = {
                base: {
                    color: '#32325d',
                    fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                    fontSmoothing: 'antialiased',
                    fontSize: '16px',
                    '::placeholder': {
                        color: '#aab7c4'
                    }
                },
                invalid: {
                    color: '#fa755a',
                    iconColor: '#fa755a'
                }
            };

            var card = elements.create('card', {style: style});

            card.mount('#card-element');
            card.addEventListener('change', function (event) {
                var displayError = document.getElementById('card-errors');
                if (event.error) {
                    displayError.textContent = event.error.message;
                } else {
                    displayError.textContent = '';
                }
            });
        }


        $('#vendor-signup-form').submit(function (e) {
            var $signupButton = $(this);
            $signupButton.attr("disabled", true);
            e.preventDefault();
            var dataForm = $('#vendor-signup-form');
            var isAbn = null;
            if ($("#tax_number").val() == 'ABN') {
                isAbn = isValidAbn($("input[name='businessapn']").val());
                if (!isAbn) {
                    $("input[name='businessapn']").val('');
                    $("#businessapn-error").text('Please enter a valid ABN number eg. 53004085616.');
                    $("#businessapn-error").show();
                    $signupButton.removeAttr("disabled");
                    return false;
                }
            }

            if (dataForm.validation('isValid')) {
                var country_id = $("#country").val();
                $("<input />").attr("type", "hidden")
                    .attr("name", "country_id")
                    .attr("value", country_id)
                    .appendTo("#vendor-signup-form");

                if (!!subscriptionChild && typeof stripe !== 'undefined') {
                    stripe.createToken(card).then(function (result) {
                        if (result.error) {
                            // Inform the user if there was an error.
                            var errorElement = document.getElementById('card-errors');
                            errorElement.textContent = result.error.message;
                            console.log(result.error.message);
                            $signupButton.removeAttr("disabled");
                        } else {
                            // Send the token to your server.
                            var token = result.token;
                            console.log(token);

                            // Insert the token ID into the form so it gets submitted to the server
                            var form = document.getElementById('vendor-signup-form');
                            var hiddenInput = document.createElement('input');
                            hiddenInput.setAttribute('type', 'hidden');
                            hiddenInput.setAttribute('name', 'card_token');
                            hiddenInput.setAttribute('value', token.id);
                            form.appendChild(hiddenInput);

                            formSubmit();
                        }
                    });

                } else {

                    formSubmit();

                }
            } else {
                $signupButton.removeAttr("disabled");
            }
        });

        $("#show-vendor-popup").click(function (e) {
            $("body").addClass("signup-show");
        });
        $("#cancel-signup").click(function (e) {
            $("#vendor-signup-form")[0].reset();
            $(".input-class-check .mage-error").hide();
            $("body").removeClass("signup-show");
        });

        var dataForm = $('#vendor-signup-form');
        dataForm.mage(
            'validation', {
                "rules": {
                    "contactnumber": {
                        "required": true,
                        'pattern': /^(?=.*[0-9])[- +()0-9]+$/,
                        "minlength": 6,
                        "maxlength": 20
                    }
                }
            });
        $("#country").on("change", function (e) {
            var countryVal = $("#country").val();

            var taxNumber = {
                US: ["EIN"],
                AU: ["ABN", "ACN", "Not registered for GST"],
                NZ: ["NZBN", "NZCN"],
                ZA: ["CIPC", "SARSNZ"]
            }
            var taxNumberArr = ["US", "AU", "NZ", "ZA"];

            if (taxNumberArr.includes(countryVal)) {
                document.getElementById('tax_number').style.display = 'block';
                document.getElementById("taxnumber-apl").style.display = "block";
                document.getElementById("tax_number").setAttribute("data-validate", "{required:true}");

                var selectedCountry = $('select#tax_number').children("option:selected").val();
                if ($.inArray(selectedCountry, taxNumber[countryVal]) === -1 || countryVal !== 'AU'){
                    var taxNumberOptions = "<option value=''>Tax Name</option>";
                    for (categoryId in taxNumber[countryVal]) {
                        taxNumberOptions += "<option>" + taxNumber[countryVal][categoryId] + "</option>";
                    }
                    document.getElementById("tax_number").innerHTML = taxNumberOptions;
                }
            } else {
                document.getElementById('tax_number').style.display = 'none';
                document.getElementById("taxnumber-apl").style.display = "none";
                $("#tax_number .mage-error").hide();
                document.getElementById("tax_number").classList.remove("mage-error");
                document.getElementById("tax_number").removeAttribute("data-validate");

            }
        });

        $('#tax_number').on('change', function () {
            if ($(this).val() == "Not registered for GST") {
                $('#businessapn').removeAttr("data-validate");
                $('#businessapn').removeClass('mage-error');
            } else {
                $('#businessapn').attr("data-validate", "{required:true}");
            }
        });

        $("#country").trigger('change');
    }
})
