<?php

//Если домен .ru то язык русский, в остальных случаях en
if (isset($_SERVER['HTTP_HOST']) && strstr($_SERVER['HTTP_HOST'], '.ru')) {
    $lang = ['ru-RU', 'ru'];
} else {
    $lang = ['en-US', 'en'];
}

return [
    'vendorPath'     => dirname(dirname(__DIR__)) . '/vendor',
    'sourceLanguage' => $lang[0],
    'language'       => $lang[1],
    'timeZone'       => 'Asia/Baghdad',
    'charset'        => 'utf-8',
    'components'     => [
        'authManager'           => [
            'class'        => 'yii\rbac\DbManager',
            'defaultRoles' => ['guest'],
        ],
        'formatter'             => [
            'decimalSeparator'  => '.',
            'thousandSeparator' => '',
        ],
        'cache'                 => [
            'class' => 'yii\caching\FileCache',
        ],
        'sms'                   => [
            'class'      => 'common\components\sms\Sms',
            'provider'   => 'common\components\sms\providers\Qtelecom',
            'attributes' => [
                'user'     => '37251.4',
                'pass'     => '27816749',
                'sender'   => 'MixCart',
                'hostname' => 'service.qtelecom.ru',
                'on_ssl'   => false,
                'period'   => false,
                'post_id'  => 'x1209448',
                'path'     => '/public/http/'
            ]
        ],
        'amo'                   => [
            'class' => 'common\components\AmoCRM',
            'url'   => 'https://fkeeper.amocrm.ru',
            'email' => 'zalina@f-keeper.ru',
            'hash'  => '6dab1d5a50b87036c49f4cd1e1593dfb',
        ],
        'user'                  => [
            'class'         => 'amnah\yii2\user\components\User',
            'identityClass' => 'common\models\User',
        ],
        'view'                  => [
            'theme' => [
                'pathMap' => [
                    '@vendor/amnah/yii2-user/views' => '@frontend/views/user',
                ],
            ],
        ],
        'resourceManager'       => [
            'class'       => 'common\components\resourcemanager\AmazonS3ResourceManager',
            'bucket'      => 'fkeeper',
            'region'      => 'eu-west-1',
            'credentials' => [
                'key'    => 'AKIAJZH26ZXTQSLVFT6A',
                'secret' => '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',
            ],
        ],
        'resourceManagerStatic' => [
            'class'       => 'common\components\resourcemanager\AmazonS3ResourceManager',
            'bucket'      => 'fkeeper',
            'region'      => 'eu-west-1',
            'credentials' => [
                'key'    => 'AKIAJZH26ZXTQSLVFT6A',
                'secret' => '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',
            ],
        ],
        'i18n'                  => [
            'translations' => [
                'kvexport'     => [
                    'class' => 'yii\i18n\PhpMessageSource'
                ],
//                'user' => [
//                    'class' => 'yii\i18n\PhpMessageSource'
//                ],
                'backend'      => [
                    'class' => 'yii\i18n\PhpMessageSource'
                ],
//                'prequest' => [
//                    'class' => 'yii\i18n\PhpMessageSource'
//                ],
                'yii'          => [
                    'class' => 'yii\i18n\PhpMessageSource'
                ],
                'app'          => [
                    'class' => 'yii\i18n\DbMessageSource',
                    //'on missingTranslation' => ['common\components\TranslationEventHandler', 'handleMissingTranslation']
                ],
                'message'      => [
                    'class' => 'yii\i18n\DbMessageSource',
                    //'on missingTranslation' => ['common\components\TranslationEventHandler', 'handleMissingTranslation']
                ],
                'sms_message'  => [
                    'class' => 'yii\i18n\DbMessageSource',
                    //'on missingTranslation' => ['common\components\TranslationEventHandler', 'handleMissingTranslation']
                ],
                'error'        => [
                    'class' => 'yii\i18n\DbMessageSource',
                    //'on missingTranslation' => ['common\components\TranslationEventHandler', 'handleMissingTranslation']
                ],
                'api_web'      => [
                    'class' => 'yii\i18n\DbMessageSource',
                ],
                'yii2mod.rbac' => [
                    'class'    => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@yii2mod/rbac/messages',
                ],
            ],
        ],
//        'mailer' => [
//            'viewPath' => '@common/mail',
//            'class' => 'common\components\Mailer',
//            'useFileTransport' => false,
//            'access_key' => '',
//            'secret_key' => '',
//            'host' => 'email.eu-west-1.amazonaws.com',
//            'messageConfig' => [
//                'from' => ['noreply@mixcart.ru' => 'noreply@mixcart.ru'],
//                'charset' => 'UTF-8',
//            ],
//        ],
        'urlManager'            => [
            'class'                        => 'codemix\localeurls\UrlManager',
            //Список языков, какая тут очередность, так и будет выводиться в виджите
            'languages'                    => ['en', 'ru', 'es', 'md', 'ua'],
            //Определение языка по заголовкам
            'enableLanguageDetection'      => false,
            //Выводить язык по умолчанию в URL
            'enableDefaultLanguageUrlCode' => false,
            // Hide index.php
            'showScriptName'               => false,
            // Use pretty URLs
            'enablePrettyUrl'              => true,
            'rules'                        => [
                '/'                                                      => 'site/index',
                'client'                                                 => 'client/index',
                'vendor'                                                 => 'vendor/index',
                'login'                                                  => 'user/login',
                'business'                                               => 'user/default/business',
                'logout'                                                 => 'user/logout',
                'register'                                               => 'user/register',
                'forgot'                                                 => 'user/forgot',
                'resend'                                                 => 'user/resend',
                'reset'                                                  => 'user/reset',
                'unsubscribe/<token:.+?>'                                => 'site/unsubscribe',
                'orders'                                                 => 'order/index',
                'product/<id:\d+>'                                       => 'site/product',
                'restaurant/<id:\d+>'                                    => 'site/restaurant',
                'supplier/<id:\d+>'                                      => 'site/supplier',
                'category/<slug:[a-z0-9_-]+>'                            => 'site/category',
                '<controller:\w+>/<id:\d+>'                              => '<controller>/view',
                '<controller:[a-z0-9_-]+>/<action:[a-z0-9_-]+>/<id:\d+>' => '<controller>/<action>',
                '<controller:[a-z0-9_-]+>/<action:[a-z0-9_-]+>'          => '<controller>/<action>',
                '<module:\w+>/<controller:\w+>/<action:\w+>/<id:\d+>'    => '<module>/<controller>/<action>/<id>',
                '<module:\w+>/<controller:\w+>/<action:\w+>'             => '<module>/<controller>/<action>',
            ],
        ],
        'urlManagerFrontend'    => [
            'class'           => 'yii\web\urlManager',
            'baseUrl'         => '//app.mixcart.ru',
            'enablePrettyUrl' => true,
            'showScriptName'  => false,
            'rules'           => [
                'product/<id:\d+>'                                       => 'site/product',
                'restaurant/<id:\d+>'                                    => 'site/restaurant',
                'supplier/<id:\d+>'                                      => 'site/supplier',
                'category/<slug:[a-z0-9_-]+>'                            => 'site/category',
                '<controller:\w+>/<id:\d+>'                              => '<controller>/view',
                '<controller:[a-z0-9_-]+>/<action:[a-z0-9_-]+>/<id:\d+>' => '<controller>/<action>',
                '<controller:[a-z0-9_-]+>/<action:[a-z0-9_-]+>'          => '<controller>/<action>',
                '<module:\w+>/<controller:\w+>/<action:\w+>/<id:\d+>'    => '<module>/<controller>/<action>/<id>',
                '<module:\w+>/<controller:\w+>/<action:\w+>'             => '<module>/<controller>/<action>',
                '/'                                                      => 'site/index',
                'client'                                                 => 'client/index',
                'vendor'                                                 => 'vendor/index',
                'about'                                                  => 'site/about',
                'faq'                                                    => 'site/faq',
                'contacts'                                               => 'site/contacts',
                'supplier'                                               => 'site/supplier',
                'restaurant'                                             => 'site/restaurant',
                'login'                                                  => 'user/login',
                'business'                                               => 'user/default/business',
                'logout'                                                 => 'user/logout',
                'register'                                               => 'user/register',
                'forgot'                                                 => 'user/forgot',
                'resend'                                                 => 'user/resend',
                'reset'                                                  => 'user/reset',
            ],
        ],
        'urlManagerFranchise'   => [
            'class'           => 'yii\web\urlManager',
            'baseUrl'         => '//partner.mixcart.ru',
            'enablePrettyUrl' => true,
            'showScriptName'  => false,
        ],
        'urlManagerWebApi'      => [
            'class'                   => \codemix\localeurls\UrlManager::className(),
            'baseUrl'                 => '//api-web.mixcart.ru',
            'enableLocaleUrls'        => false,
            'showScriptName'          => false,
            'enablePrettyUrl'         => true,
            'enableLanguageDetection' => true
        ],
        'assetManager'          => [
            'bundles' => [
                'dosamigos\google\maps\MapAsset' => [
                    'options' => [
                        'key'      => 'AIzaSyAiQcjJZXRr6xglrEo3yT_fFRn-TbLGj_M',
                        'language' => 'ru',
                        'version'  => '3.1.18'
                    ]
                ]
            ]
        ],
        //Google firebase cloud messaging
        'fcm'                   => [
            'class'  => 'understeam\fcm\Client',
            'apiKey' => 'AAAADvq3Ss8:APA91bFB5zGZpz01LtWYpMS5wwMDSjnmlv4bWYLJgJHBmQauzW24bHDG__ECgMGElVZqFV_I2MTPG2aCsV7HXshwq4yjupX1xGbuShGAyxtf7fIiepmHhFkLpxfkA4cKcCEufA3H7_Bb', // Server API Key (you can get it here: https://firebase.google.com/docs/server/setup#prerequisites)
        ],
        'google'                => [
            'class'  => 'common\components\GoogleShortUrl',
            'apiKey' => 'AIzaSyBBFwzatN-rVz6kESUAziVngA-T3_0W6Pk',
        ],
        'siteApi'               => [
            'class'   => 'mongosoft\soapclient\Client',
            'url'     => 'https://soap-api.e-vo.ru/soap/exite.wsdl',
            'options' => [
                'cache_wsdl' => WSDL_CACHE_BOTH,
            ],
        ],
        'siteApiKorus'          => [
            'class'   => 'mongosoft\soapclient\Client',
            'url'     => 'https://edi-ws.esphere.ru/edi.wsdl',
            'options' => [
                'cache_wsdl' => WSDL_CACHE_BOTH,
            ],
        ],
        //Rabbit MQ
        'rabbit'                => [
            'class'    => 'console\modules\daemons\components\RabbitService',
            'host'     => '192.168.0.100', #host - имя хоста, на котором запущен сервер RabbitMQ
            'port'     => 5672, #port - номер порта сервиса, по умолчанию - 5672
            'user'     => 'login', #user - имя пользователя для соединения с сервером
            'password' => 'password',
            'vhost'    => '/prod'
        ],
        'jwt'                   => [
            'class' => 'sizeg\jwt\Jwt',
            'key'   => 'DDA19FBF32BC8D66A5D3A22EA15F7',
        ],
        'encode'                => [
            'class' => 'common\components\Encode',
        ],
    ],
    'modules'        => [
        'user'         => [
            'class'           => 'amnah\yii2\user\Module',
            'loginEmail'      => true,
            'requireEmail'    => true,
            'requireUsername' => false,
            'loginUsername'   => false,
            'controllerMap'   => [
                'default' => 'frontend\controllers\UserController',
            ],
            'modelClasses'    => [
                'User'         => 'common\models\User',
                'Profile'      => 'common\models\Profile',
                'Role'         => 'common\models\Role',
                'Organization' => 'common\models\Organization',
                'LoginForm'    => 'common\models\forms\LoginForm',
            ],
            'emailViewPath'   => '@common/mail',
        ],
        'gridview'     => [
            'class' => 'kartik\grid\Module',
        ],
        'clientintegr' => [
            'class'  => 'frontend\modules\clientintegr\Module',
            'layout' => '@frontend/views/layouts/main-client.php',
        ],
        'vendorintegr' => [
            'class'  => 'frontend\modules\vendorintegr\Module',
            'layout' => '@frontend/views/layouts/main-vendor.php',
        ],
        'treemanager'  => [
            'class' => '\kartik\tree\Module',
            // enter other module properties if needed
            // for advanced/personalized configuration
            // (refer module properties available below)
        ],
        'pdfjs'        => [
            'class' => '\yii2assets\pdfjs\Module',
        ],
    ],
];
