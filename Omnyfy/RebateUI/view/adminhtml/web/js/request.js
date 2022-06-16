 /*
 * *
 *  * @author HTCMage Team
 *  * @copyright Copyright (c) 2020 HTCMage (https://www.htcmage.com)
 *  * @package HTCMage_PromotionBar
 *
 */

define([
    'jquery',
    "mage/template",
    'Magento_Ui/js/modal/modal'
], function ($, mageTemplate, modal) {
    'use strict';
    $.widget('omnyfy.RequestChangeRebate', {
        _create: function () {
        	var seft = this;
        	var vendorId = this.options.vendorId;
        	var url = this.options.url;
            var urlAction = this.options.urlAction;
            $(document).on("click",".request-change-of-rebate",function(event) {
            	event.preventDefault();
            	var id = $(this).data('id');
            	var name = $(this).parents(".admin__page-section-item-content").prev().children(".title").text();
            	var percentage = $(this).parents().prev().children(".rebate-value").text();
            	seft._initPopup(id, name, percentage);
			});

			$(document).on("click",".submit-change-rebate",function(event) {
            	event.preventDefault();
            	var rebateId = $(this).data('id');
            	var percentageUp = $('input[name = "rebate_change_value"]').val();
                var floatRegex = /^((\d+(\.\d *)?)|((\d*\.)?\d+))$/;
                if (floatRegex.test(percentageUp) && percentageUp <= 100 && percentageUp > 0) {
                    seft._sendAjax(url, vendorId, rebateId, percentageUp);
                }else{
                     $(".err").text('*Percentage rebate must be a number greater than 0 and less than 100.')
                }
			});

            $(document).on("click",".change-request-action .button-action .action",function(event) {
                event.preventDefault();
                var elementData = $(this).parent().prev();
                var percentage = elementData.data('percentage');
                var vendorRebateId = elementData.data('rebate-id');
                var action = 1;
                if ($(this).hasClass('action-cancel')) {
                    action = 0;
                }
                var elementUpdateHtml = $(this).parent().parent();
                var elementUpdatePercentage = $(this).parent().parent().prev().children('input').first();
                seft._sendAjaxAction(urlAction, action, vendorRebateId, percentage, elementUpdateHtml, elementUpdatePercentage);
            });
        },
        _initPopup: function (id, name, percentage) {
        	var seft = this;
            var options = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                title: '',
                buttons: []
            };
            seft._setTemplate($('#popup-modal-change-request'), id, name, percentage);
		    var popup = modal(options, $('#popup-modal-change-request'));
        	$('#popup-modal-change-request').modal('openModal');
        },

        _setTemplate: function (element, id, name, percentage) {
            var template = mageTemplate('#change-request-template');

            var newField = template({
                data: {
                	id: id,
                    name: name,
                    percentage: percentage,
                }
            });
            element.html(newField);
        },
        _sendAjax: function (url, vendorId, rebateId, percentageUp) {
            var seft = this;
            $('body').trigger('processStart');
            $.ajax({
                type: 'post',
                url: url,
                showLoader: true,
                data: {vendorId: vendorId, rebateId: rebateId, percentageUp: percentageUp},
                dataType: 'json',
                success: function (result) {
                    $('body').trigger('processStop');
                    location.reload();
                }
            });
        },
        _setTemplateReponse: function (element, action, content) {
            var template = mageTemplate('#change-request-reponse-template');

            var newField = template({
                data: {
                    action: action,
                    content: content,
                }
            });
            element.html(newField);
        },
        _sendAjaxAction: function (urlAction, action, vendorRebateId, percentageUp, elementUpdate, elementUpdatePercentage) {
            var seft = this;
            $('body').trigger('processStart');
            $.ajax({
                type: 'post',
                url: urlAction,
                showLoader: true,
                data: {vendorRebateId: vendorRebateId, action: action, percentageUp: percentageUp},
                dataType: 'json',
                success: function (result) {
                    $('body').trigger('processStop');
                    seft._setTemplateReponse(elementUpdate, result.action, result.content);
                    if (result.action == "active") {
                        elementUpdatePercentage.val(result.percentage);
                    }
                }
            });
        },

    });

    return $.omnyfy.RequestChangeRebate;
});
