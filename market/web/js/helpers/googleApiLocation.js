function initAutocomplete() {
    var acInputs = document.getElementsByClassName('autocomplete');
    var options = {
      types: ['(cities)'],
      //componentRestrictions: {country: 'ru'}
     };
    var geocoder = new google.maps.Geocoder;
    if(document.getElementById('locality').value.length < 2){
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function (position) {
                var pos = {lat: parseFloat(position.coords.latitude),
                    lng: parseFloat(position.coords.longitude)};
                    geocodeLatLng(geocoder, pos);
            },
            function (failure) {
                $.getJSON('https://ipinfo.io/geo', function (response) {
                    var loc = response.loc.split(',');
                    var pos = {lat: parseFloat(loc[0]),
                        lng: parseFloat(loc[1])};
                    geocodeLatLng(geocoder, pos);
                });
            });
        } else {
            $.getJSON('https://ipinfo.io/geo', function (response) {
                var loc = response.loc.split(',');
                var pos = {lat: parseFloat(loc[0]),
                    lng: parseFloat(loc[1])};
                geocodeLatLng(geocoder, pos);
            });
        }
    }
        for (var i = 0; i < acInputs.length; i++) {
    
        var autocomplete = new google.maps.places.Autocomplete(acInputs[i], options);
        autocomplete.inputId = acInputs[i].id;
        
            google.maps.event.addListener(autocomplete, 'place_changed', function () {
            var address_components=this.getPlace().address_components;
            var setCountry;
            var setLocality;
            var setRegion;
            
            for(var j =0 ;j<address_components.length;j++)
            {
                if(address_components[j].types[0]=='country')
                {
                    setCountry = address_components[j].long_name;
                    if(setCountry=='undefined'){setCountry = '';}
                }
                if(address_components[j].types[0]=='locality')
                {
                    setLocality = address_components[j].long_name;
                    if(setLocality=='undefined'){setLocality = '';}
                    document.getElementById('setLocality').innerHTML = setLocality;
                    document.getElementById('locHeader').innerHTML = setLocality;
                } 
                if(address_components[j].types[0]=='administrative_area_level_1')
                {
                    setRegion = address_components[j].long_name; 
                    if(setRegion=='undefined'){setRegion = '';}
                }   
           }
           document.getElementById('country').value = setCountry;
           document.getElementById('locality').value = setLocality;
           document.getElementById('administrative_area_level_1').value = setRegion;

        });
    }
}
function geocodeLatLng(geocoder, latlng) {
  geocoder.geocode({'location': latlng}, function(results, status) {
  if (status === 'OK') {
    if (results[1]) {
    var setCountry;
    var setLocality;
    var setRegion;
        for (var i = 0; i < results[1].address_components.length; i++)
        {
            var addr = results[1].address_components[i];
            if (addr.types[0] == 'country')
            setCountry = addr.long_name;
            if(setCountry=='undefined'){setCountry = '';}
            if (addr.types[0] == 'locality')
            setLocality = addr.long_name;
            if(setLocality=='undefined'){setLocality = '';}
            if (addr.types[0] == 'administrative_area_level_1')
            setRegion = addr.long_name;
            if(setRegion=='undefined'){setRegion = '';}

        }            
        document.getElementById('setLocality').innerHTML = setLocality;
        document.getElementById('locHeader').innerHTML = setLocality;

        document.getElementById('country').value = setCountry;
        document.getElementById('locality').value = setLocality;
        document.getElementById('administrative_area_level_1').value = setRegion;
    } else {
      console.log('No results found');
    }
  } else {
    console.log('Geocoder failed due to: ' + status);
  }
});
}