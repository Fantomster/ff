<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Organization */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="organization-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'white_list')->checkbox(['maxlength' => true]) ?>

    <?= $form->field($model, 'partnership')->checkBox(['maxlength' => true]) ?>

    <?= $form->field($model, 'legal_entity')->textInput(['maxlength' => true]) ?>
    <style>#map {
            width: 100%;
            height: 250px;
        }</style>
    <?= 'адрес в базе: ' . $model->address; ?>
    <?= $form->field($model, 'address')->textInput(['maxlength' => true]) ?>
    <div id="map"></div>
    <script type="text/javascript">

        function stopRKey(evt) {
            var evt = (evt) ? evt : ((event) ? event : null);
            var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null);
            if ((evt.keyCode == 13) && (node.type == "text")) {
                return false;
            }
        }

        document.onkeypress = stopRKey;

    </script>
    <?= Html::activeHiddenInput($model, 'lat'); //широта  ?>
    <?= Html::activeHiddenInput($model, 'lng'); //долгота  ?>
    <?= Html::activeHiddenInput($model, 'country'); //страна  ?>
    <?= Html::activeHiddenInput($model, 'locality'); //Город  ?>
    <?= Html::activeHiddenInput($model, 'route'); //улица  ?>
    <?= Html::activeHiddenInput($model, 'street_number'); //дом  ?>
    <?= Html::activeHiddenInput($model, 'place_id'); //уникальный индификатор места  ?>
    <?= Html::activeHiddenInput($model, 'formatted_address'); //полный адрес  ?>
    <?= Html::activeHiddenInput($model, 'administrative_area_level_1'); //область  ?>
    <?php
    $gpJsLink = 'https://maps.googleapis.com/maps/api/js?' . http_build_query([
            'libraries' => 'places',
            'key'       => Yii::$app->params['google-api']['key-id'],
            'language'  => Yii::$app->params['google-api']['language'],
            'callback'  => 'initMap'
        ]);
    $this->registerJsFile($gpJsLink, ['async' => true, 'defer' => true]);
    $this->registerJs("
    function initMap() {
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

    ", yii\web\View::POS_END);
    ?>
    <?= $form->field($model, 'zip_code')->textInput(['maxlength' => true]) ?>

    <?= ''//$form->field($model, 'phone')->widget(\yii\widgets\MaskedInput::className(), ['mask' => '+7 (999) 999 99 99',])->textInput(['maxlength' => true])  ?>

    <?=
    $form->field($model, 'phone')->widget(\common\widgets\phone\PhoneInput::className(), [
        'jsOptions' => [
            'preferredCountries' => ['ru'],
            'nationalMode'       => false,
            'utilsScript'        => Yii::$app->assetManager->getPublishedUrl('@bower/intl-tel-input') . '/build/js/utils.js',
        ],
        'options'   => [
            'class' => 'form-control',
        ]
    ])
    ?>

    <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'website')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'contact_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'blacklisted')->dropDownList(common\models\Organization::getStatusList())->label('Статус') ?>

    <?= $form->field($model, 'about')->textarea() ?>

    <?= $form->field($franchiseeModel, 'franchisee_id')->dropDownList($franchiseeList,
        ['options' =>
             [
                 (isset($model->franchisee->id)) ? $model->franchisee->id : null => ['selected' => true]
             ]
        ])->label('Название франшизы') ?>


    <?php if ($model->type_id == \common\models\Organization::TYPE_SUPPLIER): ?>
        <?= $form->field($model, 'is_work')->dropDownList(['1' => 'Да', '0' => 'Нет'])->label('Поствщик работает в системе') ?>
    <?php endif; ?>

    <?= Html::activeHiddenInput($franchiseeModel, 'organization_id', ['value' => $model->id]); ?>


    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
