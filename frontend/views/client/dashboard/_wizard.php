<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

$this->registerJs('
    function stopRKey(evt) { 
        var evt = (evt) ? evt : ((event) ? event : null); 
        var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null); 
        if ((evt.keyCode == 13) && (node.type=="text")) {return false;} 
    } 

    document.onkeypress = stopRKey; 

    $(".next").on("click", function(e) {
        e.preventDefault();
        $(".data-modal .modal-content").slick("slickNext");
    });
    
    $(".wizard-off").on("click", function(e) {
        $.ajax({
            async: false,
            type: "POST",
            url: "'.Url::to('/site/ajax-wizard-off').'"
        });
    });

    $("#complete-form").on("submit", function() {
        return false;
    });

    $("#complete-form").on("afterValidate", function(event, messages, errorAttributes) {
        console.log(messages);
        for (var input in messages) {
            if (messages[input] != "") {
                $("#" + input).tooltip({title: messages[input], placement: "auto right", container: "body"});
                $("#" + input).tooltip();
                $("#" + input).tooltip("show");
                return;
            }
        }
        $(".data-modal .modal-content").slick("slickNext");
    });

    $("#data-modal").on("shown.bs.modal",function(){
        $(".data-modal .modal-content").slick({arrows:!1,dots:!1,swipe:!1,infinite:!1,adaptiveHeight:!0})
    });
    $("#data-modal").length>0&&$("#data-modal").modal({backdrop: "static", keyboard: false});
',yii\web\View::POS_READY);

$gpJsLink= 'https://maps.googleapis.com/maps/api/js?' . http_build_query(array(
        'libraries' => 'places',
        'key'=>Yii::$app->params['google-api']['key-id'],
        'language'=>Yii::$app->params['google-api']['language'],
        'callback'=>'initMap'
    ));
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
        
	var map = new google.maps.Map(document.getElementById('map'), {
	    mapTypeId: google.maps.MapTypeId.ROADMAP
	});
        var input = document.getElementById('organization-address');
        var searchBox = new google.maps.places.SearchBox(input);
        
        searchBox.addListener('places_changed', function() {
          var places = searchBox.getPlaces();
          
          if (places.length == 0) {
            return;
          }
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
",yii\web\View::POS_BEGIN);
$this->registerJsFile($gpJsLink, ['depends' => [yii\web\JqueryAsset::className()],'async'=>true, 'defer'=>true]);
?>
<div id="data-modal" class="modal fade data-modal">
    <div class="modal-dialog">
        <button type="button" data-dismiss="modal" class="close hidden"></button>
        <div class="modal-content">
            <div class="first-step">
                <div class="data-modal__logo"><img src="images/tmp_file/logo.png" alt=""></div>
                <div class="data-modal__sub-txt">Простите за неудобства, но для корректной работы в системе<br>нам требуется получить от Вас еще несколько данных.</div>
                <?php
                $form = ActiveForm::begin([
                            'id' => 'complete-form',
                            'enableAjaxValidation' => true,
                            'enableClientValidation' => false,
                            'validateOnSubmit' => true,
                            'action' => Url::to('/site/ajax-complete-registration'),
                            'options' => [
                                'class' => 'auth-sidebar__form form-check data',
                            ],
                            'fieldConfig' => ['template' => '{input}'],
                ]);
                ?>
                <?= Html::activeHiddenInput($organization, 'lat'); //широта ?>
                <?= Html::activeHiddenInput($organization, 'lng'); //долгота ?>
                <?= Html::activeHiddenInput($organization, 'country'); //страна ?> 
                <?= Html::activeHiddenInput($organization, 'locality'); //Город ?>
                <?= Html::activeHiddenInput($organization, 'route'); //улица ?>
                <?= Html::activeHiddenInput($organization, 'street_number'); //дом ?>
                <?= Html::activeHiddenInput($organization, 'place_id'); //уникальный индификатор места ?>
                <?= Html::activeHiddenInput($organization, 'formatted_address'); //полный адрес ?>
                <div class="auth-sidebar__form-brims">
                    <label>
                        <?=
                                $form->field($profile, 'full_name')
                                ->label(false)
                                ->textInput(['class' => 'form-control', 'placeholder' => 'ФИО']);
                        ?>
                        <i class="fa fa-user"></i>
                    </label>
                    <label>
                        <?=
                                $form->field($organization, 'name')
                                ->label(false)
                                ->textInput(['class' => 'form-control', 'placeholder' => 'Название организации']);
                        ?>
                        <i class="fa fa-bank"></i>
                    </label>
                    <label>
                        <?=
                                $form->field($organization, 'address')
                                ->label(false)
                                ->textInput(['class' => 'form-control', ' onsubmit' => 'return false', 'placeholder' => 'Адрес'])
                        ?>
                        <i class="fa fa-map-marker"></i>
                    </label>
                </div>
                <div id="map" class="modal-map"></div>
                <button type="submit" class="but but_green complete-reg"><span>Продолжить работу</span><i class="ico"></i></button>
                <?php ActiveForm::end(); ?>
            </div>
            <div class="second-step">
                <div class="data-modal__icons-wrp">
                    <div class="row">
                        <div class="col-md-6 col-xs-6"><i class="ico ico-delivery"></i></div>
                        <div class="col-md-6 col-xs-6"><i class="ico ico-basket"></i></div>
                    </div>
                </div>
                <div class="data-modal__sub-txt">Вы хотите работать со своими поставщиками или найти новых?</div>
                <div class="data-modal__buts-wrp">
                    <a href="#" class="search-new but but_green wt next"><span>Найти новых</span></a>
                    <a href="<?= Url::to('client/add-first-supplier') ?>" class="but but_green wizard-off"><span>Завести своих поставщиков</span></a>
                </div>
            </div>
            <div class="third-step">
                <div class="data-modal__icons-wrp">
                    <div class="row">
                        <div class="col-md-6"><i class="ico ico-tel"></i></div>
                        <div class="col-md-6"><i class="ico ico-cart"></i></div>
                    </div>
                </div>
                <div class="data-modal__sub-txt">Вы можете создать заявку на конкретный продукт,<br>поставщики сами Вас найдут.<br>Или найти продуктов и поставщиков на f-market</div>
                <div class="data-modal__buts-wrp">
                    <a href="<?= Url::to('request/list') ?>" class="but but_green wt wizard-off"><span>Создать заявку</span></a>
                    <a href="https://market.f-keeper.ru" class="but but_green"><span>Поиск на f-market</span></a>
                </div>
            </div>
        </div>
    </div>
</div>