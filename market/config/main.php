<?php

$params = array_merge(
        require(__DIR__ . '/../../common/config/params.php'), 
        require(__DIR__ . '/../../common/config/params-local.php'), 
        require(__DIR__ . '/params.php'), 
        require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'f-market',
    'name' => 'f-market',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log',],// 'assetsAutoCompress'
    'controllerNamespace' => 'market\controllers',
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-fk',
        ],
        'session' => [
            // this is the name of the session cookie used for login on the frontend
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
//        'assetsAutoCompress' =>
//        [
//            'class'         => '\skeeks\yii2\assetsAuto\AssetsAutoCompressComponent',
//        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'formatter' => [
            'datetimeFormat' => 'MM/dd/yyyy HH:mm',
            'timeFormat' => 'HH:mm',
        ],
    ],
    'params' => $params,
];



/*$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-market',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'market\controllers',
    'bootstrap' => ['log'],
    'modules' => [],
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-fk',
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

    ],
    'params' => $params,
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
*/