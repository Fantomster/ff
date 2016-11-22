<?php
return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'sourceLanguage'=>'ru_ru',
    'language'=>'ru',
    'charset'=>'utf-8',
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
                    '@vendor/amnah/yii2-user/views' => '@app/views/user',
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
            'emailViewPath' => '@app/mail',
        ],
		'gridview' => [
			'class' => 'kartik\grid\Module',
		]

    ],
            
];
