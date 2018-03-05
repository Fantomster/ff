<?php
namespace frontend\assets;

use Yii;
use Yii\web\AssetBundle;
 
class SlickCarouselAsset extends AssetBundle
{
    public $sourcePath = '@bower/slick-carousel/slick';
    public $js = [
        'slick.min.js',
    ];
}
