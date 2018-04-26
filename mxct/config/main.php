<?php

$params = array_merge(
        require __DIR__ . '/../../common/config/params.php', require __DIR__ . '/../../common/config/params-local.php', require __DIR__ . '/params.php', require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-mxct',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'mxct\controllers',
    'bootstrap' => ['log'],
    'modules' => [],
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-mxct',
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-mxct', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the mxct
            'name' => 'advanced-mxct',
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
        'urlManager' => [
            'class' => 'yii\web\urlManager',
            'baseUrl' => 'http://app.mixcart.ru',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'product/<id:\d+>' => 'site/product',
                'restaurant/<id:\d+>' => 'site/restaurant',
                'supplier/<id:\d+>' => 'site/supplier',
                'category/<slug:[a-z0-9_-]+>' => 'site/category',
                '<controller:\w+>/<id:\d+>' => '<controller>/view',
                '<controller:[a-z0-9_-]+>/<action:[a-z0-9_-]+>/<id:\d+>' => '<controller>/<action>',
                '<controller:[a-z0-9_-]+>/<action:[a-z0-9_-]+>' => '<controller>/<action>',
                '<module:\w+>/<controller:\w+>/<action:\w+>/<id:\d+>' => '<module>/<controller>/<action>/<id>',
                '<module:\w+>/<controller:\w+>/<action:\w+>' => '<module>/<controller>/<action>',
                '/' => 'site/index',
                'client' => 'client/index',
                'vendor' => 'vendor/index',
                'about' => 'site/about',
                'faq' => 'site/faq',
                'contacts' => 'site/contacts',
                'supplier' => 'site/supplier',
                'restaurant' => 'site/restaurant',
                'login' => 'user/login',
                'business' => 'user/default/business',
                'logout' => 'user/logout',
                'register' => 'user/register',
                'forgot' => 'user/forgot',
                'resend' => 'user/resend',
                'reset' => 'user/reset',
            ],
        ],
    ],
    'params' => $params,
];
