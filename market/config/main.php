<?php

$params = array_merge(
        require(__DIR__ . '/../../common/config/params.php'), require(__DIR__ . '/../../common/config/params-local.php'), require(__DIR__ . '/params.php'), require(__DIR__ . '/params-local.php')
);

return [
    'id'                  => 'mixmarket',
    'name'                => 'mixmarket',
    'basePath'            => dirname(__DIR__),
    'bootstrap'           => ['log',], // 'assetsAutoCompress'
    'controllerNamespace' => 'market\controllers',
    'components'          => [
        'authManager'        => null,
        'request'            => [
            'csrfParam' => '_csrf-fk',
        ],
        'session'            => [
            // this is the name of the session cookie used for login on the frontend
            'name' => 'FKEEPSESSID',
        ],
        'errorHandler'       => [
            'errorAction' => 'site/error',
        ],
        'formatter'          => [
            'datetimeFormat' => 'MM/dd/yyyy HH:mm',
            'timeFormat'     => 'HH:mm',
        ],
        'urlManagerFrontend' => [
            'class'           => 'yii\web\urlManager',
            'baseUrl'         => '//mixcart.ru',
            'enablePrettyUrl' => true,
            'showScriptName'  => false,
        ],
    ],
    'params'              => $params,
];
