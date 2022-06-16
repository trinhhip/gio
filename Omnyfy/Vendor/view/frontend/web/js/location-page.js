define(
    [
        'jquery',
        'Magento_Ui/js/modal/modal'
    ],
    function($, modal){

        $.widget('omnyfy_vendor.location', {
            options: {
                changeLocationModal: '#change-location-popup'
            },

            modalOptions: {
                type: 'popup',
                responsive: false,
                innerScroll: false,
                modalClass: 'change-location-modal',
                title: 'Other Locations'
            },

            _create: function(){
                var self=this;

                self._changeLocationInit();
                console.log('abc');
            },

            _changeLocationInit: function() {
                var self=this;
                var modalEl = $(self.options.changeLocationModal);
                var popup = modal(self.modalOptions, modalEl);

                $("#change-location-btn").on('click', function(e){
                    e.preventDefault();
                    modalEl.modal('openModal');
                })
                
                
            }

            
        });

        return $.omnyfy_vendor.location;
    }
);