<?php
 
namespace frontend\assets;
 
class SweetAlertAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/sweetalert2/dist';
    public $css = [
        'sweetalert2.css',
    ];
    public $js = [
        'sweetalert2.min.js'
    ];
    public $depends = [
        'frontend\assets\CoreJsAsset',
    ];
}