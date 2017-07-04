<?php

namespace frontend\assets;

use Yii;
use Yii\web\AssetBundle;

class GoogleMapsAsset extends AssetBundle {

    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $js = [
        'js/helpers/googleMap.js',
    ];

    public static function register($view) {
        parent::register($view);
        $gpJsLink = 'https://maps.googleapis.com/maps/api/js?' . http_build_query(array(
                    'libraries' => 'places',
                    'key' => Yii::$app->params['google-api']['key-id'],
                    'language' => Yii::$app->params['google-api']['language'],
                    'callback' => 'initMap'
        ));
        Yii::$app->view->registerJsFile($gpJsLink, ['depends' => [yii\web\JqueryAsset::className()], 'async' => true, 'defer' => true, 'position' => \yii\web\View::POS_END]);
        Yii::$app->view->registerJs("try{google.maps.event.trigger(map, 'resize');}catch(e){}",\yii\web\View::POS_END);
    }
}
