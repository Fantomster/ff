<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use common\models\Organization;

if (!Yii::$app->user->isGuest) {
    $user = Yii::$app->user->identity;
    $organization = $user->organization;
}
?>
<style>
  @media (min-width: 768px) {
    ul.nav li.dropdown:hover ul.dropdown-menu{
    display: block;    
    }
  }
  @media (max-width: 767px) {
    ul.dropdown-menu {
        position: relative;
        top: 100%;
        left: 0;
        z-index: 1000;
        display: block;
        float: none; 
        min-width: 160px;
        padding: 5px 0;
        margin: 2px 0 0;
        font-size: 14px;
        list-style: none;
        background-color: none;
        background:none;
        border: none;
        box-shadow: none;
    }
    li.dropdown a span.caret{
        display:none;
    }
    .dropdown-menu > li > a:hover, .dropdown-menu > li > a:focus {
        color: #fff;
        text-decoration: none;
        background-color: none;
        background:none;
    }
    .dropdown-menu > li > a {
        text-align: center;
        color:#fff;
        font-size: 12px;
        font-family: "HelveticaBold",Arial,sans-serif;
    }
  }
  @media (min-width: 768px) {
    .navbar-inverse .navbar-nav li:nth-child(3) a{padding-bottom:6px;
    font-size: 12px;
    font-family: "HelveticaBold",Arial,sans-serif;}
    .navbar-inverse .navbar-nav li:nth-child(3) a:hover{      
        border:none;
    }
  }
  #locHeader{
    font-size: 19px;
    color: #84bf76;
    position: absolute;
    margin-top: 20px;
    margin-left: 5px;
    line-height: 18px;
    border-bottom: 1px dotted;    
  }
</style>
<section>
    <nav class="navbar navbar-inverse navbar-static-top example6 shadow-bottom">
        <div class="container" style="padding: 9px 30px">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar6">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand text-hide" href="<?= Url::home(); ?>">f-keeper</a>
            </div>
            <div id="navbar6" class="navbar-collapse collapse"><span id="locHeader"><?=Yii::$app->session->get('locality')?></span>
                <ul class="nav navbar-nav navbar-right">
                    <li><a href="<?= Url::to(['site/restaurants']) ?>">РЕСТОРАНЫ</a></li>
                    <li><a href="<?= Url::to(['site/suppliers']) ?>">ПОСТАВЩИКИ</a></li>
                    <li class="dropdown">
                        <a href="<?= Yii::$app->urlManagerFrontend->createUrl(['site/index']); ?>" class="dropdown-toggle">F-KEEPER <span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <li><a href="<?= Yii::$app->urlManagerFrontend->createUrl(['site/about']) ?>">О&nbsp;нас</a></li>
                            <li><a href="<?= Yii::$app->urlManagerFrontend->createUrl(['site/contacts']) ?>">Контакты</a></li>
                        </ul>
                      </li>
                    
                    <?php if (Yii::$app->user->isGuest) { ?>
                        <li><a class="btn-navbar" href="<?= Url::to(['/user/login']) ?>">войти / регистрация</a></li>
                    <?php } else { ?>
                        <?php if ($organization->type_id == Organization::TYPE_RESTAURANT) { ?>
                            <li>
                                <a href="<?= Yii::$app->urlManagerFrontend->createUrl(['order/checkout']) ?>">
                                    КОРЗИНА <sup><span class="badge cartCount"><?= $organization->getCartCount() ?></span></sup>
                                </a>
                            </li>
                        <?php } ?>
                        <li><a class="btn-navbar" href="<?= Url::to(['/user/logout']) ?>" data-method="post"><?= $user->profile->full_name ?> [выход]</a></li>
                        <?php } ?>
                </ul>
            </div>
            <!--/.nav-collapse -->
        </div>
        <!--/.container -->
    </nav>
</section>
<?php 
//\frontend\assets\GoogleMapsAsset::register($this);
if (empty(Yii::$app->session->get('locality')) || empty(Yii::$app->session->get('country'))) {
$this->registerJs("
  $(\"#data-modal\").length>0&&$(\"#data-modal\").modal({backdrop: \"static\", keyboard: false});
",yii\web\View::POS_END);    
}
?>
<?php

$this->registerJs("
  function initAutocomplete() {
    var acInputs = document.getElementsByClassName('autocomplete');
    var options = {
      types: ['(cities)'],
      //componentRestrictions: {country: 'ru'}
     };
    var geocoder = new google.maps.Geocoder;
    ",yii\web\View::POS_END);
if (empty(Yii::$app->session->get('locality')) || empty(Yii::$app->session->get('country'))) {
$this->registerJs("
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
",yii\web\View::POS_END);
}
$this->registerJs("
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
",yii\web\View::POS_END);
$gpJsLink= 'https://maps.googleapis.com/maps/api/js?' . http_build_query(array(
    'libraries' => 'places',
    'key'=>Yii::$app->params['google-api']['key-id'],
    'language'=>Yii::$app->params['google-api']['language'],
    'callback'=>'initAutocomplete'
));
$this->registerJsFile($gpJsLink, ['async'=>true, 'defer'=>true]);
?>
<?php
echo $this->render("../site/main/_userLocation");
$userLocation = Url::to(['/site/location-user']);
$customJs = <<< JS
$(document).on("click","#locHeader", function () { 
    $("#data-modal").length>0&&$("#data-modal").modal({backdrop: "static", keyboard: false});
});       
JS;
$this->registerJs($customJs, View::POS_READY);
?>
