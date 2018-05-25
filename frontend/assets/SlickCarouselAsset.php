<?php
namespace frontend\assets;

use Yii\web\AssetBundle;
 
class SlickCarouselAsset extends AssetBundle
{
    public $sourcePath = '@bower/slick-carousel/slick';
    public $css = [
        'slick.css'
    ];
    public $js = [
        'slick.min.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
    public $publishOptions = [
        'forceCopy' => true
    ];
}
