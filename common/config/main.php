<?php

return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'sourceLanguage' => 'ru_ru',
    'language' => 'ru',
//    'timeZone' => 'Europe/Moscow',
    'timeZone' => 'GMT+3',
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
        ],
        'mailqueue' => [
            'class' => 'nterms\mailqueue\MailQueue',
            'table' => '{{%mail_queue}}',
            'mailsPerRound' => 15,
            'maxAttempts' => 1,
            'viewPath' => '@common/mail',
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
            ],
            'emailViewPath' => '@common/mail',
        ],
        'gridview' => [
            'class' => 'kartik\grid\Module',
        ],
    ],
];
