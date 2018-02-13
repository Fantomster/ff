function initMap() {
    try {

        var fields = {
            sField: document.getElementById('organization-address'),
            hLat: document.getElementById('organization-lat'),
            hLng: document.getElementById('organization-lng'),
            hCountry: document.getElementById('organization-country'),
            hLocality: document.getElementById('organization-locality'),
            hAdministrativeAreaLevel1: document.getElementById('organization-administrative_area_level_1'),
            hPlaceId: document.getElementById('organization-place_id'),
            hRoute: document.getElementById('organization-route'),
            hStreetNumber: document.getElementById('organization-street_number'),
            hFormattedAddress: document.getElementById('organization-formatted_address')
        };

        //инит карты
        var map = new google.maps.Map(document.getElementById('map'), {
            mapTypeId: google.maps.MapTypeId.ROADMAP
        });
        // Create the search box and link it to the UI element.
        var input = document.getElementById('organization-address');
        var searchBox = new google.maps.places.SearchBox(input);

        var autocomplete = new google.maps.places.Autocomplete(input);
        autocomplete.bindTo('bounds', map);

        autocomplete.addListener('place_changed', function () {
            var place = autocomplete.getPlace();
            var bounds = new google.maps.LatLngBounds();
            if (!place.geometry) {
                console.log('Returned place contains no geometry');
                return;
            }
            if (place.geometry.viewport) {
                bounds.union(place.geometry.viewport);
            } else {
                bounds.extend(place.geometry.location);
            }

            marker.setPosition(place.geometry.location);
            changeFields(fields, [place]);
            map.fitBounds(bounds);
            map.setZoom(17);
            map.panTo(place.geometry.location);
        });

        searchBox.addListener('places_changed', function () {
            var places = searchBox.getPlaces();

            if (places.length == 0) {
                return;
            }
            // For each place, get the icon, name and location.
            var bounds = new google.maps.LatLngBounds();
            places.forEach(function (place) {
                if (!place.geometry) {
                    console.log('Returned place contains no geometry');
                    return;
                }
                if (place.geometry.viewport) {
                    bounds.union(place.geometry.viewport);
                } else {
                    bounds.extend(place.geometry.location);
                }

            })
            if (places[0].address_components) {
                marker.setPosition(places[0].geometry.location);
                changeFields(fields, places)
            }
            map.fitBounds(bounds);
            map.setZoom(17);
        })



        //инит маркера
        var marker = new google.maps.Marker({
            map: map,
            draggable: true
        });
        var geocoder = new google.maps.Geocoder;

        if (typeof fields.hPlaceId.value == 'undefined' || fields.hPlaceId.value == '') {
            geolocation(map, marker, fields)
        } else {
            geocodePlaceId(geocoder, map, marker, String(fields.hPlaceId.value), fields)
        }

        //событие на перемещение маркера
        marker.addListener('dragend', function (e) {
            geocoder.geocode({'latLng': e.latLng}, function (results, status) {
                if (status == 'OK') {
                    if (results[0]) {
                        map.panTo(results[0].geometry.location);
                        marker.setPosition(results[0].geometry.location);
                        changeFields(fields, results)
                    }
                } else {
                    console.log('[dragger] Geocoder failed due to: ' + status);
                }
            });
        })

        //Событие на клик по карте
        map.addListener('click', function (e) {
            geocoder.geocode({'latLng': e.latLng}, function (results, status) {
                if (status == 'OK') {
                    if (results[0]) {
                        map.panTo(e.latLng);
                        marker.setPosition(e.latLng);
                        changeFields(fields, results)
                    }
                } else {
                    console.log('[click] Geocoder failed due to: ' + status);
                }
            })
        });

        google.maps.event.addListener(map, 'idle', function ()
        {
            google.maps.event.trigger(map, 'resize');
        });
    } catch (e) {
        //
    }
}
//Если нам известин placeId тогда выводим все данные
function geocodePlaceId(geocoder, map, marker, placeId, fields) {
    geocoder.geocode({'placeId': placeId}, function (results, status) {
        if (status === 'OK') {
            if (results[0]) {
                map.setZoom(17);
                map.panTo(results[0].geometry.location);
                marker.setPosition(results[0].geometry.location);
                changeFields(fields, results)
            } else {
                console.log('[PlaceId] No results found');
            }
        } else {
            console.log('[geocodePlaceId]  failed due to: ' + status);
        }
    });
}
//геолокация по ip или геолокации из браузера
function geolocation(map, marker, fields) {
    fields.sField.value = '';
    fields.hLat.value = '';
    fields.hLng.value = '';
    fields.hCountry.value = '';
    fields.hLocality.value = '';
    fields.hAdministrativeAreaLevel1.value = '';
    fields.hRoute.value = '';
    fields.hStreetNumber.value = '';
    fields.hPlaceId.value = '';
    fields.hFormattedAddress.value = '';
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function (position) {
            var pos = {lat: parseFloat(position.coords.latitude),
                lng: parseFloat(position.coords.longitude)};
            map.setZoom(9);
            map.panTo(pos);
            marker.setPosition(pos);
        },
                function (failure) {
                    $.getJSON('https://ipinfo.io/geo', function (response) {
                        var loc = response.loc.split(',');
                        var pos = {lat: parseFloat(loc[0]),
                            lng: parseFloat(loc[1])};
                        map.setZoom(9);
                        map.panTo(pos);
                        marker.setPosition(pos);
                    });
                });
    } else {
        window.alert('Geolocation failed');
    }
}
//Сохранение полученных данных в хидден поля
function changeFields(fields, results) {
    for (var i = 0; i < results[0].address_components.length; i++)
    {
        var addr = results[0].address_components[i];
        var getCountry;
        var getLocality;
        var getRoute;
        var getStreetNumber;
        var getAdministrative_area_level_1;
        var getAdministrative_area_level_2;
        var getFormattedAddress = results[0].formatted_address;
        var getLat = results[0].geometry.location.lat();
        var getLng = results[0].geometry.location.lng();
        var getPlaceId = results[0].place_id;

        if (addr.types[0] == 'country')
            getCountry = addr.long_name;
        if (addr.types[0] == 'locality')
            getLocality = addr.long_name;
        if (addr.types[0] == 'route')
            getRoute = addr.long_name;
        if (addr.types[0] == 'street_number')
            getStreetNumber = addr.long_name;
        if (addr.types[0] == 'administrative_area_level_2')
            getAdministrative_area_level_2 = addr.long_name;
        if (addr.types[0] == 'administrative_area_level_1')
            getAdministrative_area_level_1 = addr.long_name;
    }
    if (results[0]) {
        var res = '';
        typeof getRoute == 'undefined' ? '' :
                res = getRoute;
        typeof getStreetNumber == 'undefined' ? '' :
                res = res + ', ' + getStreetNumber;
        typeof getLocality == 'undefined' ? '' :
                res = res + ', ' + getLocality;
        typeof getAdministrative_area_level_2 == 'undefined' ? '' :
                res = res + ', ' + getAdministrative_area_level_2;
        typeof getAdministrative_area_level_1 == 'undefined' ? '' :
                res = res + ', ' + getAdministrative_area_level_1;
        typeof getCountry == 'undefined' ? '' :
                res = res + ', ' + getCountry;
        fields.sField.value = res;
        fields.hLat.value = getLat;
        fields.hLng.value = getLng;
        fields.hCountry.value = getCountry;
        fields.hLocality.value = getLocality;
        fields.hAdministrativeAreaLevel1.value = getAdministrative_area_level_1;
        fields.hRoute.value = getRoute;
        fields.hStreetNumber.value = getStreetNumber;
        fields.hPlaceId.value = getPlaceId;
        fields.hFormattedAddress.value = getFormattedAddress;
    } else {
        alert('Geocode was not successful for the following reason: ' + status);
    }
}
