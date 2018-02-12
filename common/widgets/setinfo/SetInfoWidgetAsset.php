<?php

namespace common\widgets\setinfo;

use yii\web\AssetBundle;

/**
 * Assets for SetInfoWidget
 *
 * @author elbabuino
 */

class SetInfoWidgetAsset extends AssetBundle  {
    public $sourcePath = '@common/widgets/set-info/assets';
    public $css = [
        'css/info_popup.css',
    ];

    public $depends = [
        'common\assets\HelpersAsset',
    ];
    
    public function init()
    {
        $this->sourcePath = __DIR__ . "/assets";
        parent::init();
    }
}
