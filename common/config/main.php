<?php

return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'sourceLanguage' => 'ru_ru',
    'language' => 'ru',
    'timeZone' => 'Asia/Baghdad',
    'charset' => 'utf-8',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'class' => 'amnah\yii2\user\components\User',
            'identityClass' => 'common\models\User',
        ],
        'view' => [
            'theme' => [
                'pathMap' => [
                    '@vendor/amnah/yii2-user/views' => '@frontend/views/user',
                ],
            ],
        ],
        'resourceManager' => [
            'class' => 'dosamigos\resourcemanager\AmazonS3ResourceManager',
            'key' => 'AKIAIQWR4FTPYC2CM6QQ',
            'secret' => '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',
            'bucket' => 'fkeeper',
        ],
        'i18n' => [
            'translations' => [
                '*' => [
                    'class' => 'yii\i18n\PhpMessageSource'
                ],
            ],
        ],
        'formatter' => [
            'locale' => 'ru_RU',
        ],
        'mailer' => [
            'viewPath' => '@common/mail',
            'class' => 'common\components\Mailer',
            'useFileTransport' => false,
            'access_key' => 'AKIAIFLSS7TR5MOL64WQ',
            'secret_key' => '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',
            'host' => 'email.eu-west-1.amazonaws.com',
            'messageConfig' => [
                'from' => ['noreply@f-keeper.ru' => 'noreply@f-keeper.ru'],
                'charset' => 'UTF-8',
            ],
        ],
        'urlManager' => [
            'class' => 'yii\web\UrlManager',
            // Hide index.php
            'showScriptName' => false,
            // Use pretty URLs
            'enablePrettyUrl' => true,
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
        'urlManagerFrontend' => [
            'class' => 'yii\web\urlManager',
            'baseUrl' => '//f-keeper.ru',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
        ],
        'urlManagerFranchise' => [
            'class' => 'yii\web\urlManager',
            'baseUrl' => '//partner.f-keeper.ru',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
        ],
        'assetManager' => [
            'bundles' => [
                'dosamigos\google\maps\MapAsset' => [
                    'options' => [
                        'key' => 'AIzaSyAiQcjJZXRr6xglrEo3yT_fFRn-TbLGj_M',
                        'language' => 'ru',
                        'version' => '3.1.18'
                    ]
                ]
            ]
        ],
        //Google firebase cloud messaging
        'fcm' => [
            'class' => 'understeam\fcm\Client',
            'apiKey' => 'AAAADvq3Ss8:APA91bFB5zGZpz01LtWYpMS5wwMDSjnmlv4bWYLJgJHBmQauzW24bHDG__ECgMGElVZqFV_I2MTPG2aCsV7HXshwq4yjupX1xGbuShGAyxtf7fIiepmHhFkLpxfkA4cKcCEufA3H7_Bb', // Server API Key (you can get it here: https://firebase.google.com/docs/server/setup#prerequisites)
        ],
    ],
    'modules' => [
        'user' => [
            'class' => 'amnah\yii2\user\Module',
            'loginEmail' => true,
            'requireEmail' => true,
            'requireUsername' => false,
            'loginUsername' => false,
            'controllerMap' => [
                'default' => 'frontend\controllers\UserController',
            ],
            'modelClasses' => [
                'User' => 'common\models\User',
                'Profile' => 'common\models\Profile',
                'Role' => 'common\models\Role',
                'Organization' => 'common\models\Organization',
                'LoginForm' => 'common\models\forms\LoginForm',
            ],
            'emailViewPath' => '@common/mail',
        ],
        'gridview' => [
            'class' => 'kartik\grid\Module',
        ],
        'clientintegr' => [
            'class' => 'frontend\modules\clientintegr\Module',
            'layout' => '@frontend/views/layouts/main-client.php',
        ],
        'vendorintegr' => [
            'class' => 'frontend\modules\vendorintegr\Module',
            'layout' => '@frontend/views/layouts/main-vendor.php',
        ],
        'treemanager' =>  [
            'class' => '\kartik\tree\Module',
            // enter other module properties if needed
            // for advanced/personalized configuration
            // (refer module properties available below)
        ]
    ],
];