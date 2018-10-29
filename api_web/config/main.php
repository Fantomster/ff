<?php

$params = array_merge(
        require(__DIR__ . '/../../common/config/params.php'), require(__DIR__ . '/../../common/config/params-local.php'), require(__DIR__ . '/params.php'), file_exists(__DIR__ . '/params-local.php') ? require(__DIR__ . '/params-local.php') : []
);

return [
    'id'                  => 'mixcart_web_api',
    'name'                => 'mixcart_web_api',
    'basePath'            => dirname(__DIR__),
    'controllerNamespace' => 'api_web\controllers',
    'defaultRoute'        => 'site',
    'components'          => [
        'request'      => [
            'class'               => \yii\web\Request::className(),
            'cookieValidationKey' => '__absoluteExpire112233',
            'parsers'             => [
                'application/json' => 'yii\web\JsonParser',
                'text/plain'       => 'yii\web\JsonParser',
            ]
        ],
        'urlManager'   => [
            'class'                   => \codemix\localeurls\UrlManager::className(),
            'enableLocaleUrls'        => false,
            'showScriptName'          => false,
            'enablePrettyUrl'         => true,
            'enableLanguageDetection' => true,
            'rules'                   => [
                'integration/vetis/<action>' => '/integration/vetis/default/<action>',
            ],
        ],
        'user'         => [
            'class'         => \amnah\yii2\user\components\User::className(),
            'identityClass' => \common\models\User::className(),
            'loginUrl'      => null,
            'enableSession' => false,
        ],
        'view'         => [
            'theme' => [
                'pathMap' => [
                    '@vendor/amnah/yii2-user/views' => '@api_web/views/user',
                ],
            ],
        ],
        'session'      => [
            'name' => 'advanced-web-api',
        ],
        'errorHandler' => [
            'class' => \api_web\handler\WebApiErrorHandler::className(),
        ]
    ],
    'modules'             => [
        'user'        => [
            'class'         => '\amnah\yii2\user\Module',
            'loginEmail'    => true,
            'loginUsername' => false,
            'controllerMap' => [
                'default' => 'api_web\controllers\UserController',
            ],
        ],
        'integration' => [
            'class' => 'api_web\modules\integration\Module',
        ]
    ],
    'params'              => $params,
];
