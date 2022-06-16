
    window.onload = function () {
        initialize();
    };
    var placeSearch, autocomplete, autocomplete_textarea;
    var componentForm = {
        street_number: 'short_name',
        route: 'long_name',
        locality: 'long_name',
        administrative_area_level_1: 'long_name',
        country: 'short_name',
        postal_code: 'short_name'
    };
    function initialize() {
    // Create the autocomplete object, restricting the search
    // to geographical location types.
    setTimeout(function () {
            autocomplete = new google.maps.places.Autocomplete(
                (document.getElementsByName("businessaddress")[0]),
                    {types: ['geocode']}
            );
            google.maps.event.addListener(autocomplete, 'place_changed', function () {
                var place = autocomplete.getPlace();


                var addressDetail = {};
                // Get each component of the address from the place details
                // and fill the corresponding field on the form.
                for (var i = 0; i < place.address_components.length; i++) {
                    var addressType = place.address_components[i].types[0];

                    if (componentForm[addressType]) {
                        var val = place.address_components[i][componentForm[addressType]];
                        addressDetail[addressType] = val;
                    }
                }

                document.getElementsByName("city")[0].value = (typeof addressDetail.locality === 'undefined') ? '' : addressDetail.locality;
                document.getElementsByName("postcode")[0].value = (typeof addressDetail.postal_code === 'undefined') ? '' : addressDetail.postal_code;
                document.getElementsByName("state")[0].value = (typeof addressDetail.administrative_area_level_1 === 'undefined') ? '' : addressDetail.administrative_area_level_1;
                document.getElementById("country").value = (typeof addressDetail.country == 'undefined') ? '' : addressDetail.country;

                // hidden input to save vendor lat lng
                let vendorLat = document.getElementById('vendor_latitude');
                let vendorLng = document.getElementById('vendor_longitude');
                if(vendorLat && vendorLng){
                    document.getElementById('vendor_latitude').value = (typeof place.geometry.location.lat() == 'undefined') ? '' : place.geometry.location.lat();
                    document.getElementById('vendor_longitude').value = (typeof place.geometry.location.lng() == 'undefined') ? '' : place.geometry.location.lng();
                }

				if(document.getElementById('city-error')){
					document.getElementById('city-error').style.display  = 'none';
					document.getElementById("city").classList.remove("mage-error");
				}
				if(document.getElementById('city-error')){
					document.getElementById('state-error').style.display  = 'none';
					document.getElementById("state").classList.remove("mage-error");
				}
				if(document.getElementById('city-error')){
					document.getElementById('postcode-error').style.display  = 'none';
					document.getElementById("postcode").classList.remove("mage-error");
				}

				var taxNumber  = {
					US: ["EIN"],
                    AU: ["ABN", "ACN", "Not registered for GST"],
					NZ: ["NZBN", "NZCN"],
					ZA: ["CIPC", "SARSNZ"]
				}
				var taxNumberArr = ["US", "AU", "NZ", "ZA"];

				if (taxNumberArr.includes(addressDetail.country)){
                    var taxElement = document.getElementById("tax_number");
                    var taxValue =  taxElement.options[taxElement.selectedIndex].text;

					document.getElementById('tax_number').style.display  = 'block';
					document.getElementById("taxnumber-apl").style.display = "block";
					document.getElementById("tax_number").setAttribute("data-validate", "{required:true}");
					document.getElementById("contactnumber-apl").className = "col-sm-8";
					/* $("#contactnumber-apl").attr('class', 'col-sm-8');
					$("#contactnumber-apl").toggleClass("col-sm-8");
					$("#contactnumber-apl").removeClass("col-sm-12");
					$("#contactnumber-apl").addClass("col-sm-8"); */
					var taxNumberOptions = "<option value=''>Tax Name</option>";
    for (categoryId in taxNumber[addressDetail.country]) {
                        if(taxValue == taxNumber[addressDetail.country][categoryId]){
                            taxNumberOptions += "<option selected>" + taxNumber[addressDetail.country][categoryId] + "</option>";
                        }else{
        taxNumberOptions += "<option>" + taxNumber[addressDetail.country][categoryId] + "</option>";
                        }
    }
    document.getElementById("tax_number").innerHTML = taxNumberOptions;
    } else {
        document.getElementById('tax_number').style.display  = 'none';
        document.getElementById("taxnumber-apl").style.display = "none";
        /* $("#tax_number .mage-error").hide(); */
        document.getElementById("tax_number").classList.remove("mage-error");
        document.getElementById("tax_number").removeAttribute("data-validate");
        /* $("#contactnumber-apl").attr('class', 'col-sm-12'); */
        document.getElementById("contactnumber-apl").className = "col-sm-12";
        /* $("#contactnumber-apl").removeClass("col-sm-8");
        $("#contactnumber-apl").addClass("col-sm-12"); */
    }
    });
    }, 1000);
    }
