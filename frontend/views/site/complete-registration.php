<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\checkbox\CheckboxX;
kartik\checkbox\KrajeeFlatBlueThemeAsset::register($this);
/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var amnah\yii2\user\models\forms\LoginForm $model
 */
$this->title = Yii::t('message', 'frontend.views.site.complete_registration', ['ru'=>"Завершение регистрации"]);
?>
<div class="login__block">
    <div class="login__inside">
        <a href="<?= Yii::$app->params['staticUrl']['home'] ?>"><img src="/images/logo-inner.png" alt=""/></a>
        <div class="contact__form">
            <?php
            $form = ActiveForm::begin([
                        'id' => 'login-form',
                        'enableAjaxValidation' => false,
                        'validateOnSubmit' => false,
            ]);
            ?>
            <div class="form-group">
        <?=
            $form->field($organization, 'address')
            ->label(false)
            ->textInput(['class' => 'form-control',' onsubmit' => 'return false', 'placeholder' => Yii::t('message', 'frontend.views.site.address', ['ru'=>'Адрес организации'])])
        ?>
        <div id="map" style="width:100%;height:250px;"></div>
<?= Html::activeHiddenInput($organization, 'lat'); //широта ?>
<?= Html::activeHiddenInput($organization, 'lng'); //долгота ?>
<?= Html::activeHiddenInput($organization, 'country'); //страна ?> 
<?= Html::activeHiddenInput($organization, 'locality'); //Город ?>
<?= Html::activeHiddenInput($organization, 'administrative_area_level_1'); //область ?>
<?= Html::activeHiddenInput($organization, 'route'); //улица ?>
<?= Html::activeHiddenInput($organization, 'street_number'); //дом ?>
<?= Html::activeHiddenInput($organization, 'place_id'); //уникальный индификатор места ?>
<?= Html::activeHiddenInput($organization, 'formatted_address'); //полный адрес ?>
<script type="text/javascript"> 

function stopRKey(evt) { 
var evt = (evt) ? evt : ((event) ? event : null); 
var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null); 
if ((evt.keyCode == 13) && (node.type=="text")) {return false;} 
} 

document.onkeypress = stopRKey; 

</script>        
<?php
$gpJsLink= 'https://maps.googleapis.com/maps/api/js?' . http_build_query(array(
        'libraries' => 'places',
        'key'=>Yii::$app->params['google-api']['key-id'],
        'language'=>Yii::$app->params['google-api']['language'],
        'callback'=>'initMap'
    ));
$this->registerJsFile($gpJsLink, ['depends' => [yii\web\JqueryAsset::className()],'async'=>true, 'defer'=>true]);
$this->registerJs("
function initMap() {
    var fields = {
            sField : document.getElementById('organization-address'),
            hLat : document.getElementById('organization-lat'),
            hLng : document.getElementById('organization-lng'),
            hCountry : document.getElementById('organization-country'),
            hLocality : document.getElementById('organization-locality'),
            hPlaceId : document.getElementById('organization-place_id'),
            hRoute : document.getElementById('organization-route'),
            hStreetNumber : document.getElementById('organization-street_number'),
            hFormattedAddress : document.getElementById('organization-formatted_address')
            };
        
	//инит карты
	var map = new google.maps.Map(document.getElementById('map'), {
	    mapTypeId: google.maps.MapTypeId.ROADMAP
	});
        // Create the search box and link it to the UI element.
        var input = document.getElementById('organization-address');
        var searchBox = new google.maps.places.SearchBox(input);
        
        searchBox.addListener('places_changed', function() {
          var places = searchBox.getPlaces();
          
          if (places.length == 0) {
            return;
          }
          // For each place, get the icon, name and location.
          var bounds = new google.maps.LatLngBounds();
          places.forEach(function(place) {
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
	            draggable:true
	});	
	var geocoder = new google.maps.Geocoder;
	
	if(typeof fields.hPlaceId.value == 'undefined' || fields.hPlaceId.value == ''){
            geolocation(map, marker, fields)
	}else{
            geocodePlaceId(geocoder, map, marker, String(fields.hPlaceId.value),fields)
        }
	
	//событие на перемещение маркера
	marker.addListener('dragend', function(e){
	    geocoder.geocode({'latLng': e.latLng}, function(results, status) {
	        if(status == 'OK') {
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
        map.addListener('click', function(e) {
            geocoder.geocode({'latLng': e.latLng}, function(results, status) {
                if(status == 'OK') {
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
}
//Если нам известин placeId тогда выводим все данные
function geocodePlaceId(geocoder, map, marker, placeId, fields) {
    geocoder.geocode({'placeId': placeId}, function(results, status) {
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
function geolocation(map, marker, fields){
    fields.sField.value = '';
    fields.hLat.value = '';
    fields.hLng.value = '';
    fields.hCountry.value = '';
    fields.hLocality.value = '';
    fields.hRoute.value = '';
    fields.hStreetNumber.value = '';
    fields.hPlaceId.value = '';
    fields.hFormattedAddress.value = '';
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(function(position) { 
          var pos = {lat: parseFloat(position.coords.latitude), 
                     lng: parseFloat(position.coords.longitude)};
          map.setZoom(9);
          map.panTo(pos);
          marker.setPosition(pos);
          },
          function(failure) {
              $.getJSON('https://ipinfo.io/geo', function(response) { 
                  var loc = response.loc.split(',');
                  var pos = {lat: parseFloat(loc[0]),
                             lng: parseFloat(loc[1])};
                  map.setZoom(9);
                  map.panTo(pos);
                  marker.setPosition(pos);
              });  
      });
    }else{
	 window.alert('Geolocation failed');   
    }
}
//Сохранение полученных данных в хидден поля
function changeFields(fields, results){
    for (var i = 0; i < results[0].address_components.length; i++)
        {
          var addr = results[0].address_components[i];
          var getCountry;
          var getLocality;
          var getRoute;
          var getStreetNumber;
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

        }
        if(results[0]) {
        var res = '';
        typeof getRoute == 'undefined' ?'':
            res = getRoute;
        typeof getStreetNumber == 'undefined' ?'':
            res = res+', '+getStreetNumber;
        typeof getLocality == 'undefined' ?'':
            res = res+', '+getLocality;
        typeof getAdministrative_area_level_2 == 'undefined' ?'':
            res = res+', '+getAdministrative_area_level_2;
        typeof getCountry == 'undefined' ?'':
            res = res+', '+getCountry;   
        fields.sField.value = res;
        fields.hLat.value = getLat;
        fields.hLng.value = getLng;
        fields.hCountry.value = getCountry;
        fields.hLocality.value = getLocality;
        fields.hRoute.value = getRoute;
        fields.hStreetNumber.value = getStreetNumber;
        fields.hPlaceId.value = getPlaceId;
        fields.hFormattedAddress.value = getFormattedAddress;
        } else {
        alert('Geocode was not successful for the following reason: ' + status);
        }    
}
",yii\web\View::POS_END);
?>
            </div>
            <?=
            Html::a(Yii::t('message', 'frontend.views.site.submit', ['ru'=>'Подтвердить']), '#', [
                'data' => [
                    'method' => 'post',
                ],
                'class' => 'send__btn',
            ])
            ?>
            <input type="submit" style="position: absolute; left: -9999px; width: 1px; height: 1px;" tabindex="-1" />
            <?php ActiveForm::end(); ?>
        </div>
    </div>

</div>