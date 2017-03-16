<section id="location">
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <div class="input-group">
	      <span class="input-group-addon" id="addon_search">Поиск</span>
	      <input type="text" class="form-control" id="autocomplete" placeholder="введите свой адрес" onFocus="geolocate()" aria-describedby="addon_search">
	    </div>
	    <div class="input-group">
	      <span class="input-group-addon" id="addon_country">Страна</span>
	      <input type="text" class="form-control" id="country" aria-describedby="addon_country">
	    </div>
	    <div class="input-group">
	      <span class="input-group-addon" id="addon_locality">Город</span>
	      <input type="text" class="form-control" id="locality" aria-describedby="addon_locality">
	    </div>
	    <div class="input-group">
	      <span class="input-group-addon" id="addon_route">Улица</span>
	      <input type="text" class="form-control" id="route" aria-describedby="addon_route">
	    </div>
	    <div class="input-group">
	      <span class="input-group-addon" id="addon_route">Номер дома</span>
	      <input type="text" class="form-control" id="street_number" aria-describedby="addon_street_number">
	    </div>
      </div>
    </div>
  </div>
</section>    
<!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js">
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>-->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCBVFLS9LMiR5CYyONNCi7A5vh2p7l9r8M&libraries=places&callback=initAutocomplete&language=ru-RU" async defer></script>

<script>
var placeSearch, autocomplete;
var componentForm = {
  street_number: 'short_name',
  //administrative_area_level_1: 'short_name',
  //postal_code: 'short_name'
  country: 'long_name',
  locality: 'long_name',
  route: 'long_name',
};

function initAutocomplete() {
  autocomplete = new google.maps.places.Autocomplete(
      (document.getElementById('autocomplete')),
      {types: ['geocode']});
  autocomplete.addListener('place_changed', fillInAddress);
}

function fillInAddress() {
  var place = autocomplete.getPlace();

  for (var component in componentForm) {
    document.getElementById(component).value = '';
    document.getElementById(component).disabled = false;
  }
  for (var i = 0; i < place.address_components.length; i++) {
    var addressType = place.address_components[i].types[0];
    if (componentForm[addressType]) {
      var val = place.address_components[i][componentForm[addressType]];
      document.getElementById(addressType).value = val;
    }
  }
}
console.log(navigator.geolocation);
function geolocate() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(position) {
      var geolocation = {
        lat: position.coords.latitude,
        lng: position.coords.longitude
      };
      var circle = new google.maps.Circle({
        center: geolocation,
        radius: position.coords.accuracy
      });
      autocomplete.setBounds(circle.getBounds());
    });
  }
}

</script>
