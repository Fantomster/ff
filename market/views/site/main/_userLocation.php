<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\View;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
?>
<style>
.loc-block{padding:15px;text-align: center; position: relative; margin: 0 auto;z-index:99999}
.loc-h-city{font-family: sans-serif;text-transform: uppercase;color: #77a267;border-bottom: 1px dotted;}
.loc-list-cityes{text-align: center;margin-top: 20px}
.loc-submit{margin-top:20px}
.pac-container {
    z-index: 1051 !important;
}
</style>
<?php
$this->registerJs('
    function stopRKey(evt) { 
        var evt = (evt) ? evt : ((event) ? event : null); 
        var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null); 
        if ((evt.keyCode == 13) && (node.type=="text")) {return false;} 
    } 
    
    document.onkeypress = stopRKey; 
    $("#data-modal").length>0&&$("#data-modal").modal({backdrop: "static", keyboard: false});
',yii\web\View::POS_READY);
?>
<div id="data-modal" class="modal fade data-modal">
    <div class="modal-dialog" style="margin-top: 25%;">
        <button type="button" data-dismiss="modal" class="close hidden"></button>
        <div class="modal-content">
            <div class="loc-block">
                    <h3>ВАШ ГОРОД <span id="setLocality" class="loc-h-city"></span>?</h3>
                    <h5>Если мы определили не верно Ваш город, пожалуйста, найдите его самостоятельно</h5>
                    <input type="text" class="form-control autocomplete" id="search_out" name="search_out" placeholder="Поиск">
                    <button type="button" class="btn btn-md btn-success loc-submit">Подтвердить</button>
            </div>
        </div>
    </div>
</div>
<?php
$gpJsLink= 'https://maps.googleapis.com/maps/api/js?' . http_build_query(array(
    'libraries' => 'places',
    'key'=>Yii::$app->params['google-api']['key-id'],
    'language'=>Yii::$app->params['google-api']['language'],
    'callback'=>'initAutocomplete'
));
$this->registerJsFile($gpJsLink, ['async'=>true, 'defer'=>true]);
$this->registerJs("
  function initAutocomplete() {
    var acInputs = document.getElementsByClassName('autocomplete');
    var options = {
      types: ['(cities)'],
      //componentRestrictions: {country: 'ru'}
     };
    var geocoder = new google.maps.Geocoder;
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
                }
                if(address_components[j].types[0]=='locality')
                {
                    setLocality = address_components[j].long_name;
                    document.getElementById('setLocality').innerHTML = setLocality;
                    document.getElementById('locHeader').innerHTML = setLocality;
                } 
                if(address_components[j].types[0]=='administrative_area_level_1')
                {
                    setRegion = address_components[j].long_name; 
                }   
           }

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
                    if (addr.types[0] == 'locality')
                    setLocality = addr.long_name;
                    if (addr.types[0] == 'administrative_area_level_1')
                    setRegion = addr.long_name;
                    
                }            
                document.getElementById('setLocality').innerHTML = setLocality;
                document.getElementById('locHeader').innerHTML = setLocality;
            } else {
              console.log('No results found');
            }
          } else {
            console.log('Geocoder failed due to: ' + status);
          }
        });
      }	
",yii\web\View::POS_END);
?>
