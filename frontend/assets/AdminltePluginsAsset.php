<?php
namespace frontend\assets;
use yii;
use yii\web\AssetBundle;

class AdminltePluginsAsset extends AssetBundle {
    public $sourcePath = '@vendor/almasaeed2010/adminlte/plugins';
    public $js = [
        "chartjs/Chart.min.js",
    ];
}