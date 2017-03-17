<?php
 
namespace frontend\assets;
 
class TutorializeAsset extends \yii\web\AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/tutorialize.css', 
    ];
    public $js = [
        'js/jquery.tutorialize.min.js', 
    ];
    public $depends = [
        'yii\web\YiiAsset',
    ];
}