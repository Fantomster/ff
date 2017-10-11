<?php

namespace api\modules\v1\modules\mobile;

use Yii;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\filters\ContentNegotiator;
use yii\web\Response;
use common\models\forms\LoginForm;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'api\modules\v1\modules\mobile\controllers';
    
    public $controllerBehaviors;

    public function init()
    {
        parent::init();
        
        Yii::$app->set('user', [
        'class' => 'api\modules\v1\modules\mobile\components\User',
        'identityClass' => 'api\modules\v1\modules\mobile\models\User',
        'loginUrl' => null,
        'enableSession' => false,
        ]);
        
        /*Yii::$app->set(
                'mailer', [
                    'class' => 'yii\swiftmailer\Mailer',
                    'viewPath' => '@common/mail',
                    'htmlLayout' => '@common/mail/layouts/html'
                    ]);*/
        
        Yii::$app->set(
                'request', [
                        'class' => 'yii\web\Request',
                        'parsers' => [
                            'application/json' => 'yii\web\JsonParser',
                        ]
                    ]
                );
        
        Yii::$app->set(
                'urlManagerFrontEnd', [
                        'class' => 'yii\web\urlManager',
                        'baseUrl' => Yii::$app->params['maindUrl'],
                        'enablePrettyUrl' => true,
                        'showScriptName' => false,
                    ]
                );
        
         $this->controllerBehaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'only' => ['index', 'view', 'options', 'auth','complete-registration', 
                'refresh-fcm-token', 'send', 'create', 
                'viewed', 'update', 'create', 'delete', 'new-order', 'favorites', 'send'],
            'authMethods' => [
                [
                    'class' => HttpBasicAuth::className(),
                    'auth' => function ($username, $password) {
            
                        $model = new LoginForm();
                        $model->email = $username;
                        $model->password = $password;
                        return ($model->validate()) ? $model->getUser() : null;
                    }
                ],
                HttpBearerAuth::className(),
                QueryParamAuth::className()
            ]
        ];
                
        $this->controllerBehaviors['contentNegotiator'] = [
        'class' => ContentNegotiator::className(),
        'formats' => [
            'application/json' => Response::FORMAT_JSON
        ]

        ];
    }
}
