<?php

namespace api\modules\v1\modules\mobile;

use Yii;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'api\modules\v1\modules\mobile\controllers';

    public function init()
    {
        parent::init();
        
        Yii::$app->set('user', [
        'class' => 'api\modules\v1\modules\mobile\components\User',
        'identityClass' => 'api\modules\v1\modules\mobile\models\User',
        'loginUrl' => null,
        'enableSession' => false,
        ]);
        
        Yii::$app->set(
                'mailer', [
                    'class' => 'yii\swiftmailer\Mailer',
                    'viewPath' => '@common/mail',
                    'htmlLayout' => '@common/mail/layouts/html'
                    ]);
    }
}
