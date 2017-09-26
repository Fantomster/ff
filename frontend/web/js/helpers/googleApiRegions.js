function initAutocomplete() {
    
    var acInputs = document.getElementsByClassName('autocomplete');
    var options = {
      types: ['(regions)'],
      //componentRestrictions: {country: 'ru'}
     };
    for (var i = 0; i < acInputs.length; i++) {
    
        var autocomplete = new google.maps.places.Autocomplete(acInputs[i], options);
        autocomplete.inputId = acInputs[i].id;
        
            google.maps.event.addListener(autocomplete, 'place_changed', function () {

            var address_components=this.getPlace().address_components;
            
            var country='';
            var administrative_area_level_1='';
            var locality='';
            
            for(var j =0 ;j<address_components.length;j++)
            {
                if(address_components[j].types[0]=='country')
                {
                    country = address_components[j].long_name;
                }
                if(address_components[j].types[0]=='administrative_area_level_1')
                {
                    administrative_area_level_1 = address_components[j].long_name;
                }
                if(address_components[j].types[0]=='locality')
                {
                    locality = address_components[j].long_name;
                }  
            }
            if(this.inputId == 'search_in'){
                var form = document.getElementById('form-in');
                form.querySelector('input[id=\"deliveryregions-country\"]').value = country;
                form.querySelector('input[id=\"deliveryregions-administrative_area_level_1\"]').value = administrative_area_level_1;
                form.querySelector('input[id=\"deliveryregions-locality\"]').value = locality;
            }
            if(this.inputId == 'search_out'){
                var form = document.getElementById('form-out');
                form.querySelector('input[id=\"deliveryregions-country\"]').value = country;
                form.querySelector('input[id=\"deliveryregions-administrative_area_level_1\"]').value = administrative_area_level_1;
                form.querySelector('input[id=\"deliveryregions-locality\"]').value = locality;
            }
        });
    }
  }