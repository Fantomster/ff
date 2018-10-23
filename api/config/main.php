<?php

$params = array_merge(
        require(__DIR__ . '/../../common/config/params.php'), require(__DIR__ . '/../../common/config/params-local.php'), require(__DIR__ . '/params.php'), require(__DIR__ . '/params-local.php')
);

return [
    'id'                  => 'mixcart',
    'name'                => 'mixcart',
    'basePath'            => dirname(__DIR__),
    'bootstrap'           => [
        'log',
        'api\modules\v1\modules\supp\Bootstrap',
        'api\modules\v1\modules\odinsrest\Bootstrap',
        'api\modules\v1\modules\telepad\Bootstrap'
    ],
    'controllerNamespace' => 'api\common\controllers',
    'defaultRoute'        => 'site',
    'components'          => [
        'request'      => [
            'csrfParam'           => '_csrf-api',
            'cookieValidationKey' => 'SKJDHJSKY7656ast676a5s26zzz',
        ],
        'user'         => [
            'class'          => 'amnah\yii2\user\components\User',
            //        'identityClass' => 'api\common\models\User',      
            //    'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-api', 'httpOnly' => true],
        ],
        'view'         => [
            'theme' => [
                'pathMap' => [
                    '@vendor/amnah/yii2-user/views' => '@api/views/user',
                ],
            ],
        ],
        'session'      => [
            // this is the name of the session cookie used for login on the api
            'name' => 'advanced-api',
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
    ],
    'modules'             => [
        'user' => [
            'class'         => '\amnah\yii2\user\Module',
            'loginEmail'    => true,
            //   'requireEmail' => true,
            //    'requireUsername' => false,
            'loginUsername' => false,
            'controllerMap' => [
                'default' => 'api\common\controllers\UserController',
            ],
            'modelClasses'  => [
                'User'      => 'api\common\models\User',
                'Profile'   => 'api\common\models\Profile',
                'Role'      => 'api\common\models\Role',
                'UserAuth'  => 'api\common\models\UserAuth',
                'UserToken' => 'api\common\models\UserToken',
            //    'Organization' => 'api\common\models\Organization',
            //    'LoginForm' => 'api\common\models\forms\LoginForm',
            ],
            'emailViewPath' => '@api/mail',
        ],
        'v1'   => [
            'class' => 'api\modules\v1\Module',
        ]
    ],
    'params'              => $params,
];
