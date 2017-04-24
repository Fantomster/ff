<?php

$params = array_merge(
        require(__DIR__ . '/../../common/config/params.php'), require(__DIR__ . '/../../common/config/params-local.php'), require(__DIR__ . '/params.php'), require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'f-keeper',
    'name' => 'f-keeper',
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
