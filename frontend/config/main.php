<?php

$params = array_merge(
        require(__DIR__ . '/../../common/config/params.php'), require(__DIR__ . '/../../common/config/params-local.php'), require(__DIR__ . '/params.php'), require(__DIR__ . '/params-local.php')
);

return [
    'id'                  => 'mixcart',
    'name'                => 'mixcart',
    'basePath'            => dirname(__DIR__),
    'bootstrap'           => ['log',], // 'assetsAutoCompress'
    'controllerNamespace' => 'frontend\controllers',
    'components'          => [
//                'cloudWatchLogTarget' => [
//                    'class' => 'common\components\CloudWatchLogTarget',
//                    'levels' => ['error'],
//                    'cloudWatchLog' => 'cloudWatchLog',
//                    'groupName' => 'Errors_at_mixcart.test',
//                ],        
        'authManager'         => 'common\components\DbManager',
        'request'             => [
            'csrfParam'        => '_csrf-fk',
            'enableCsrfCookie' => true,
        ],
        'session'             => [
            // this is the name of the session cookie used for login on the frontend
            'name' => 'FKEEPSESSID',
        ],
        'errorHandler'        => [
            'errorAction' => 'site/error',
        ],
        'formatter'           => [
            'datetimeFormat' => 'MM/dd/yyyy HH:mm',
            'timeFormat'     => 'HH:mm',
        ],
        'urlManagerFranchise' => [
            'class'           => 'yii\web\urlManager',
            'baseUrl'         => '//partner.mixcart.ru',
            'enablePrettyUrl' => true,
            'showScriptName'  => false,
        ],
    ],
    'params'              => $params,
    'modules'             => [
        'billing' => [
            'class' => 'frontend\modules\billing\Module',
        ],
    ],
];
