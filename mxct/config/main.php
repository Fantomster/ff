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
            'class' => \codemix\localeurls\UrlManager::className(),
            'enableLocaleUrls' => false,
            'showScriptName' => false,
            'enablePrettyUrl' => true,
            'enableLanguageDetection' => true,
            'baseUrl' => 'https://mxct.ru',
            'rules' => [
                '/<token:[a-zA-Z0-9_-]+>' => 'site/index',
            ],
        ],
    ],
    'params' => $params,
];
