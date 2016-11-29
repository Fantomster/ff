<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-backend',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],
    'modules' => [],
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-backend',
        ],
        'session' => [
            // this is the name of the session cookie used for login on the backend
            'name' => 'FKEEPSESSID',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        /*
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
        */
    ],
    'params' => $params,
//    'modules' => [
//        'user' => [
//            'class' => 'amnah\yii2\user\Module',
//            'loginEmail' => true,
//            'requireEmail' => true,
//            'requireUsername' => false,
//            'loginUsername' => false, 
//            'controllerMap' => [
//                'default' => 'amnah\yii2\user\controllers\DefaultController',
//            ],
//        ],
//    ],
    'on beforeAction' => function ($event) {
        if (Yii::$app->user->isGuest) {
            if ($event->action->id !== 'login') {
                $event->isValid = false;
                Yii::$app->response->redirect(['/user/default/login']);
            }
            return;
        }
        if (!Yii::$app->user->can('admin')) {
            $event->isValid = false;
            Yii::$app->response->statusCode = 403;
        }
    },
];
