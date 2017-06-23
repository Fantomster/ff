<?php

namespace frontend\widgets\cart;

use yii\web\AssetBundle;

/**
 * Description of CartWidgetAsset
 *
 * @author fenris
 */
class CartWidgetAsset extends AssetBundle {
    public $sourcePath = '@frontend/widgets/cart/assets';
    
    public $js = [
        'js/cartwidget.js'
    ];

    public $css = [
        'css/font-awesome.min.css',
        'css/cartwidget.css',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapAsset',
    ];

    public function init()
    {
        $this->sourcePath = __DIR__ . "/assets";
        parent::init();
    }}
