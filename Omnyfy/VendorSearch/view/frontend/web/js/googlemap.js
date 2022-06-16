/**
 * Copyright © Omnyfy. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'Omnyfy_VendorSearch/js/action/submit',
    'https://polyfill.io/v3/polyfill.min.js?features=default',
    'https://unpkg.com/@google/markerclustererplus@4.0.1/dist/markerclustererplus.min.js'
], function($, submitFilter) {

    // Vendor popup for each marker
    class Popup extends google.maps.OverlayView {
        position;
        containerDiv;
        markerHeight;
        id;
        constructor(id, position, content, markerHeight) {
            super();
            this.position = position;
            this.markerHeight = markerHeight;
            this.id = id;
            content.classList.add('popup-bubble');
            // This zero-height div is positioned at the bottom of the bubble.
            const bubbleAnchor = document.createElement('div');
            bubbleAnchor.classList.add('popup-bubble-anchor');
            bubbleAnchor.appendChild(content);
            // This zero-height div is positioned at the bottom of the tip.
            this.containerDiv = document.createElement('div');
            this.containerDiv.classList.add('popup-container');
            this.containerDiv.appendChild(bubbleAnchor);
            // Optionally stop clicks, etc., from bubbling up to the map.
            Popup.preventMapHitsAndGesturesFrom(this.containerDiv);
        }
        /** Called when the popup is added to the map. */
        onAdd() {
            this.getPanes().floatPane.appendChild(this.containerDiv);
        }
        /** Called when the popup is removed from the map. */
        onRemove() {
            if (this.containerDiv.parentElement) {
                this.containerDiv.parentElement.removeChild(this.containerDiv);
            }
        }
        /** Called each frame when the popup needs to draw itself. */
        draw() {
            const divPosition = this.getProjection().fromLatLngToDivPixel(
                this.position
            );
            // Hide the popup when it is far out of view.
            const display =
                Math.abs(divPosition.x) < 4000 && Math.abs(divPosition.y) < 4000
                    ? 'block'
                    : 'none';

            if (display === 'block') {
                this.containerDiv.style.left = divPosition.x + 'px';
                this.containerDiv.style.top = 'calc(' + divPosition.y + 'px - ' + this.markerHeight / 2 + 'px)';;
            }

            if (this.containerDiv.style.display !== display) {
                this.containerDiv.style.display = display;
            }
        }

        /**
         *  Set the visibility to 'hidden' or 'visible'.
         */
        hide() {
            if (this.containerDiv) {
                this.containerDiv.style.visibility = 'hidden';
            }
        }
        show() {
            if (this.containerDiv) {
                this.containerDiv.style.visibility = 'visible';
            }
        }
        toggle() {
            if (this.containerDiv) {
                if (this.containerDiv.style.visibility === 'hidden') {
                    this.show();
                } else {
                    this.hide();
                }
            }
        }
        toggleDOM(map) {
            if (this.getMap()) {
                this.setMap(null);
            } else {
                this.setMap(map);
            }
        }
        getId(){
            return this.id;
        }

    };
    // END class Popup()

    return function(config) {
        let mapZoomDefault = JSON.parse(config.mapZoomDefault),
            mapTypeId = config.mapTypeId,
            mapStyle = JSON.parse(config.mapStyle),
            vendorIcon = config.vendorIcon,
            latLng = JSON.parse('[' + config.mapLatLng + ']');

        let mapLatLng = new google.maps.LatLng(latLng[0], latLng[1]);
        let markerHeight = config.markerHeight;
        let mapOptions = {
            gestureHandling: 'greedy',
            zoom: mapZoomDefault,
            center: mapLatLng,
            mapTypeId: mapTypeId,
            styles: mapStyle,
        };
        let map = new google.maps.Map(document.getElementById('map_show'), mapOptions);

        // init Message container
        let message = $('#maincontent');
        if (!message.find('#vendor-message-container').length) {
            message.prepend('<div id="vendor-message-container"></div>');
        } else {
            message.find('#vendor-message-container').html('');
        }

        //If user share their location
        var cname = 'used_location',
            usedLocation = getCookie(cname);
        if (navigator.geolocation) {
            if (!usedLocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const pos = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude,
                        };
                        map.setCenter(pos);
                        let latitude = pos.lat,
                            longitude = pos.lng;
                        setCookie('used_location', 1, 1);
                        processMapFilter(latitude, longitude, config);
                    },
                    () => {
                        setCookie('used_location', 1, 1);
                    }
                );
            }
        }

        // Auto-complete address function
        // const card = document.getElementById('pac-card');
        const card = document.createElement('div');
        const pacInput = document.createElement('input');
        card.id = 'pac-card';
        pacInput.id = 'pac-input';
        pacInput.placeholder = 'Search';
        let mapSearchData = getCookie('mapSearchData');
        let addressIcon = config.addressIcon;
        pacInput.value = mapSearchData ? JSON.parse(mapSearchData).requestAddress : '';
        pacInput.type = 'text';
        card.appendChild(pacInput);
        // const pacInput = document.getElementById('pac-input');
        const options = {
            fields: ['formatted_address', 'geometry', 'name'],
            strictBounds: false,
            types: [],
        };

        map.controls[google.maps.ControlPosition.TOP_CENTER].push(card);

        google.maps.InfoWindow.prototype.isOpen = function(){
            var map = this.getMap();
            return (map !== null && typeof map !== "undefined");
        }

        const autocomplete = new google.maps.places.Autocomplete(pacInput, options);
        const allowedCountries = config.allowedCountries;
        if(allowedCountries){
            autocomplete.setComponentRestrictions({
                country: allowedCountries.split(',')
            });
        }
        const infowindow = new google.maps.InfoWindow();
        const infowindowContent = document.getElementById('infowindow-content');

        infowindow.setContent(infowindowContent);

        const addressMarker = new google.maps.Marker({
            map,
            anchorPoint: new google.maps.Point(0, -29),
            icon: addressIcon
        });

        if(getCookie('mapSearchData')){
            let mapSearchData = JSON.parse(getCookie('mapSearchData'));
            addressMarker.setPosition(mapSearchData.geometry.location);
            addressMarker.setVisible(true);
            infowindowContent.children['place-name'].textContent = mapSearchData.name;
            infowindowContent.children['place-address'].textContent =
                mapSearchData.formatted_address;
            infowindow.open(map, addressMarker);
        }

        google.maps.event.addListener(addressMarker, 'click',function(){
            if(infowindow.isOpen()){
                infowindow.close();
            }else{
                infowindow.open(map, addressMarker);
            }
        });

        autocomplete.addListener('place_changed', () => {
            infowindow.close();
            addressMarker.setVisible(false);

            const place = autocomplete.getPlace();

            if (!place.geometry || !place.geometry.location) {

                if(!place.name){
                    errorText = 'Address field is empty'
                    message.find('#vendor-message-container').html(getMessageHtml(errorText, false));
                }

                return;
            }

            processMapFilter(place.geometry.location.lat(), place.geometry.location.lng(), config, place);
            $('.button-filter-show button.btn-primary').hide();
        });


        // Search this area function
        const locationButton = document.createElement('button');
        locationButton.textContent = config.btnSearchThis;
        locationButton.classList.add('custom-map-control-button', 'action', 'primary');
        map.controls[google.maps.ControlPosition.TOP_CENTER].push(locationButton);
        locationButton.addEventListener('click', () => {
            let mapCenter = map.getCenter(),
                latitude = mapCenter.lat(),
                longitude = mapCenter.lng();
            processMapFilter(latitude, longitude, config);
            $('.button-filter-show button.btn-primary').hide();
        });

        //Drag event on map
        function handleMarkerDrag() {
            if ($('.no-maker-onmap').length && $('.no-maker-onmap').is(':visible')) {
                $('.no-maker-onmap').hide();
            }
            let areaButton = $('body').find('.custom-map-control-button');
            // if (areaButton.length && areaButton.css('opacity', '0')) {
            //     areaButton.addClass('active');
            // }
            if (!$('.no-maker-onmap').length) {
                $('.button-filter-show button.btn-primary').show();
            }
        }

        google.maps.event.addListener(map, 'dragend', function() {
            handleMarkerDrag();
        });

        /**
         * Process map filter
         *
         * @param {float} latitude
         * @param {float} longitude
         * @param {array} config
         */
        function processMapFilter(latitude, longitude, config, extraInfo = null) {
            let vendorContainer = $(config.contentMapArea)
                layerContainer = $(config.layerContainer),
                vendorCounterContainer = $(config.vendorCounterContainer),
                defaultDistance = config.defaultDistance,
                mapSearchDistanceAttr = config.mapSearchDistanceAttr,
                currentUrl = config.currentUrl;
            let urlPaths = currentUrl.split('?'),
                baseUrl = urlPaths[0],
                urlParams = urlPaths[1] ? urlPaths[1].split('&') : [],
                paramData = {},
                parameters;
            for (let i = 0; i < urlParams.length; i++) {
                parameters = urlParams[i].split('=');
                paramData[parameters[0]] = parameters[1] !== undefined ?
                    window.decodeURIComponent(parameters[1].replace(/\+/g, '%20')) :
                    '';
            }
            paramData['latitude'] = latitude;
            paramData['longitude'] = longitude;
            if(!paramData[mapSearchDistanceAttr]){
                paramData[mapSearchDistanceAttr] = defaultDistance;
            }
            paramData = $.param(paramData);
            let actionUrl = baseUrl + (paramData.length ? '?' + paramData : '');
            if(extraInfo){
                extraInfo.requestAddress = pacInput.value;
                setCookie('mapSearchData', JSON.stringify(extraInfo), 1);
            } else {
                setCookie('mapSearchData', '', 1);
            }
            submitFilter(actionUrl, JSON.stringify(paramData), vendorContainer, layerContainer, vendorCounterContainer);
        }

        /**
         * Set cookie
         *
         * @param cname
         * @param cvalue
         * @param exdays
         */
        function setCookie(cname, cvalue, exdays) {
            const d = new Date();
            d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
            let expires = 'expires=' + d.toUTCString();
            document.cookie = cname + '=' + cvalue + ';' + expires + ';path=/';
        }

        /**
         * Get cookie
         *
         * @param cname
         *
         * @return mixed
         */
        function getCookie(cname) {
            let name = cname + '=';
            let decodedCookie = decodeURIComponent(document.cookie);
            let ca = decodedCookie.split(';');
            for (let i = 0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) == ' ') {
                    c = c.substring(1);
                }
                if (c.indexOf(name) == 0) {
                    return c.substring(name.length, c.length);
                }
            }
            return '';
        }

        // Set LatLng and title text for the markers. The first marker (Boynton Pass)
        // receives the initial focus when tab is pressed. Use arrow keys to
        // move between markers; press tab again to cycle through the map controls.

        var locs = config.locs;
        var i;
        const markers = [];
        const popups = [];


        if (locs.length > 0) {
            $('.button-filter-show button.btn-primary').show();
        }
        else {
            $('.button-filter-show button.btn-primary').hide();
        }
        for (i = 0; i < locs.length; i++) {
            if(!locs[i][1] || !locs[i][2])
                continue;
            let popup = new Popup(
                locs[i][0],
                new google.maps.LatLng(locs[i][1], locs[i][2]),
                document.getElementById('popup_' + locs[i][0]),
                markerHeight
            );

            const pos0 = new google.maps.LatLng(locs[i][1], locs[i][2]);
            const pos = { lat: pos0.lat(), lng: pos0.lng() };

            var marker = new google.maps.Marker({
                position: pos,
                animation: google.maps.Animation.DROP,
                icon: vendorIcon,
                map: map
            });
            popup.hide();
            popup.setMap(map);
            popups.push(popup);
            markers[locs[i][0]] = marker;
        }

        map.addListener('click', (function(popups) {
            return function() {
                popups.forEach(function (popup){
                    popup.hide();
                })
            }
        })(popups));

        markers.forEach(function (marker, id ){
            marker.addListener('click', function() {
                popups.forEach(function (popup){
                    if(popup.getId() != id){
                        popup.hide();
                    }else{
                        popup.toggle();
                    }
                });
            });
        });

        // Options to pass along to the marker clusterer
        const clusterOptions = {
            imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m',
            gridSize: 30,
            maxZoom: 10,
        };

        // Add a marker clusterer to manage the markers.
        const markerClusterer = new MarkerClusterer(map, markers, clusterOptions);

        // Change styles after cluster is created
        const styles = markerClusterer.getStyles();
        for (let i = 0; i < styles.length; i++) {
            styles[i].textColor = 'white';
            styles[i].textSize = 20;
        }

        function getMessageHtml(messageText, isSuccess) {
            var htmlClass = isSuccess ? 'success' : 'error';
            return '<div class="messages">' +
                '<div class="message message-' + htmlClass + ' ' + htmlClass + '">' +
                '<div data-ui-id="magento-framework-view-element-messages-0-message-' + htmlClass + '">' +
                messageText +
                '</div>' +
                '</div>' +
                '</div>';
        }
    };
});