require(
["jquery",
 "mage/template",
 "mage/mage",
 "Magento_Ui/js/block-loader"],
function($,mageTemplate, mage, blockLoader) {
    $(document).ready(function () {
        $(".page-wrapper").on("click","#cancel-btn", function () {
			$("textarea[name=reply-message]").val('');
		});	
        $(".page-wrapper").on("click","#reply-btn", function () {
            var enquiryId = $("input[name=enquiry-id]").val();
            var vendorId = $("input[name=vendor-id]").val();
            var customerId = $("input[name=customer-id]").val();
            var productId = $("input[name=product-id]").val();
            var ajaxUrl = $("input[name=ajax-url]").val();
            var message = $("textarea[name=reply-message]").val();
            var productName = $("input[name=product-name]").val();
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
                        customerId: customerId,
                        productId: productId,
                        message: message,
                        fromType: fromType,
                        toType: toType,
                        productName: productName
                    },
                    success: function (response) {
                        var messageTmpl = mageTemplate('#customer-conversation'), tmpl;

							tmpl = messageTmpl({
								data: {
									title: response['title'],
									message: response["enquiry_message"],
								}
							});
							$('#enquiry-messages').append(tmpl);
							$("#message-complete").show().removeClass("error");
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
    });
});
