<?php
namespace frontend\assets;

use Yii;
use Yii\web\AssetBundle;
 
class BootboxAsset extends AssetBundle
{
    public $sourcePath = '@bower/bootbox';
    public $js = [
        'bootbox.js',
    ];
    public static function overrideSystemConfirm()
    {
        Yii::$app->view->registerJs('
            yii.confirm = function(message, ok, cancel) {
                bootbox.confirm(message, function(result) {
                    if (result) { !ok || ok(); } else { !cancel || cancel(); }
                });
            }
        ');
    }
}
