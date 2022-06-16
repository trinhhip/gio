require(
["jquery",
 "mage/template",
 "mage/mage",
 "Magento_Ui/js/block-loader"],
function($,mageTemplate, mage, blockLoader) {
    $(document).ready(function () {
        $(".page-wrapper").on("click","#reply-btn", function () {
            var enquiryId = $("input[name=enquiry-id]").val();
            var vendorId = $("input[name=vendor-id]").val();
            var customerId = $("input[name=customer-id]").val();
            var productId = $("input[name=product-id]").val();
            var ajaxUrl = $("input[name=ajax-url]").val();
            var message = $("textarea[name=reply-message]").val();
            var notifyCustomer = 0;

            if ($("input[name=is_notify_customer]").prop('checked')) {
                notifyCustomer = 1;
            }

            var visibleStorefront = 0;

            if($("input[name=is_visible_frontend]").prop('checked')) {
                visibleStorefront = 1;
            }

            var fromType = $("checkbox[name=from_type]").val();
            var toType = $("checkbox[name=to_type]").val();

            if (message) {
				if(message.length<=1000){
					$.ajax({
						url: ajaxUrl,
						type: 'POST',
						showLoader: true,
						dataType: 'json',
						data: {
							enquiryId: enquiryId,
							vendorId: vendorId,
							form_key: window.FORM_KEY,
							customerId: customerId,
							productId: productId,
							message: message,
							notifyCustomer: notifyCustomer,
							visibleStorefront: visibleStorefront,
							fromType: fromType,
							toType: toType
						},
						success: function (response) {
							var messageTmpl = mageTemplate('#vendor-conversation'), tmpl;

							tmpl = messageTmpl({
								data: {
									title: response['title'],
									message: response["enquiry_message"],
								}
							});
							$('#enquiry-messages').append(tmpl);
							$('#last-updated-date').html(response["last-update"]);
							//$("#message-complete").show().addClass(response['type']).html(response['message']);
							$("textarea[name=reply-message]").val("");
						},
						fail: function () {
							$("#message-complete").show().html('Unexpected Error. Please try again.').addClass('error');
						}
					});
				} else{
					$("#message-complete").show().addClass("error").html("Please enter no more than 1000 characters.");
				}
            } else {
                $("#message-complete").show().addClass("error").html("Please fill the message.");
            }
            $("#message-complete").delay(5000).fadeOut("slow");
        });

        $(".page-wrapper").on("click","button#complete", function () {
            var enquiryId = $("input[name=enquiry-id]").val();
        });

        $(".page-wrapper").on("click","button#archive", function () {
            var enquiryId = $("input[name=enquiry-id]").val();
        });
    });
});