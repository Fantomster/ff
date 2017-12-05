<?php

namespace market\assets;

use Yii;
use Yii\web\AssetBundle;

class GoogleMapsAsset extends AssetBundle {

    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $js = [
        'js/helpers/googleApiLocation.js?v=1',
    ];
    public static function register($view) {
        parent::register($view);
        $gpJsLink = 'https://maps.googleapis.com/maps/api/js?' . http_build_query(array(
                    'libraries' => 'places',
                    'key' => Yii::$app->params['google-api']['key-id'],
                    'language' => Yii::$app->params['google-api']['language'],
                    'callback' => 'initAutocomplete'
        ));
        Yii::$app->view->registerJsFile($gpJsLink, ['depends' => [yii\web\JqueryAsset::className()], 'async' => true, 'defer' => true, 'position' => \yii\web\View::POS_END]);
    }
}
