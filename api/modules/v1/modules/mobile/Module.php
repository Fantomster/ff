<?php

namespace api\modules\v1\modules\mobile;

use Yii;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'api\modules\v1\modules\mobile\controllers';

    public function init()
    {
        parent::init();
        Yii::$app->user->identityClass = 'api\modules\v1\modules\mobile\models\ApiUserIdentity';
        Yii::$app->user->enableSession = false;
        Yii::$app->user->loginUrl = null;
    }
}
