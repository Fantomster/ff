<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'console\controllers',
    'components' => [
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'urlManager' => [
            'hostInfo' => 'https://mixcart.ru',
        ],
    ],
    'aliases' => [
          '@baseUrl'=>'https://mixcart.ru'
//        '@web' => 'http://f-keeper.dev',
//        '@webroot' => '/var/www/html/f-keeper.dev/frontend/web',
    ],
    'params' => $params,
];
