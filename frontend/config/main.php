<?php

$params = array_merge(
        require(__DIR__ . '/../../common/config/params.php'), require(__DIR__ . '/../../common/config/params-local.php'), require(__DIR__ . '/params.php'), require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'mixcart',
    'name' => 'mixcart',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log',], // 'assetsAutoCompress'
    'controllerNamespace' => 'frontend\controllers',
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
                'file' => [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
//                'email' => [
//                    'class' => 'yii\log\EmailTarget',
//                    'except' => ['yii\web\HttpException:404','yii\web\HttpException:403'],
//                    'levels' => ['error'],
//                    'message' => [
//                        'from' => 'noreply@f-keeper.ru', 
//                        'to' => ['sharap@f-keeper.ru', 'marshal1209448@gmail.com','xsupervisor@f-keeper.ru'], 
//                        'subject' => 'Error message',
//                    ],
//                    'mailer' => 'mailer',
//                ],
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
        'urlManagerFranchise' => [
            'class' => 'yii\web\urlManager',
            'baseUrl' => '//partner.mixcart.ru',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
        ],
    ],

    'params' => $params,
];
