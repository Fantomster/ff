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
    public $depends = [
        'yii\web\YiiAsset',
    ];

    public static function register($view) {
        $gpJsLink = 'https://maps.googleapis.com/maps/api/js?' . http_build_query(array(
                    'libraries' => 'places',
                    'key' => Yii::$app->params['google-api']['key-id'],
                    'language' => Yii::$app->params['google-api']['language'],
                    'callback' => 'initMap'
        ));
        Yii::$app->view->registerJsFile($gpJsLink, ['async' => true, 'defer' => true, 'position' => yii\web\View::POS_END]);
        parent::register($view);
    }
}
