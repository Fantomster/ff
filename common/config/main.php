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
//            'class' => 'yashop\ses\Mailer',
//            'access_key' => 'AKIAIFLSS7TR5MOL64WQ',
//            'secret_key' => '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',
//            'host' => 'email.eu-west-1.amazonaws.com',
//            'messageConfig' => [
//                'from' => ['noreply@f-keeper.ru' => 'noreply@f-keeper.ru'],
//                'charset' => 'UTF-8',
//            ],
        ],
        'mailqueue' => [
            'class' => 'nterms\mailqueue\MailQueue',
            'table' => '{{%mail_queue}}',
            'mailsPerRound' => 15,
            'maxAttempts' => 1,
            'viewPath' => '@common/mail',
        ],
        'urlManager' => [
            'class' => 'yii\web\UrlManager',
            // Hide index.php
            'showScriptName' => false,
            // Use pretty URLs
            'enablePrettyUrl' => true,
            'rules' => [
                '<module:\w+>/<controller:\w+>/<action:\w+>/<id:\d+>' => '<module>/<controller>/<action>/<id>',
                '<module:\w+>/<controller:\w+>/<action:\w+>' => '<module>/<controller>/<action>',
                '<controller:\w+>/<id:\d+>' => '<controller>/view',
                '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
            ],
            //'enableStrictParsing' => true,
            'rules' => [
                '/' => 'site/index',
                'client' => 'client/index',
                'vendor' => 'vendor/index',
                'about' => 'site/about',
                'faq' => 'site/faq',
                'contacts' => 'site/contacts',
                'supplier' => 'site/supplier',
                'restaurant' => 'site/restaurant',
                'login' => 'user/login',
                'logout' => 'user/logout',
                'register' => 'user/register',
                'forgot' => 'user/forgot',
                'resend' => 'user/resend',
                'reset' => 'user/reset',
            ],
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
    ],
];
