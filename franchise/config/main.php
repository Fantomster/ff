<?php

$params = array_merge(
        require(__DIR__ . '/../../common/config/params.php'), require(__DIR__ . '/../../common/config/params-local.php'), require(__DIR__ . '/params.php'), require(__DIR__ . '/params-local.php')
);

return [
    'id'                  => 'partner.f-keeper',
    'name'                => 'partner.f-keeper',
    'basePath'            => dirname(__DIR__),
    'bootstrap'           => ['log'],
    'controllerNamespace' => 'franchise\controllers',
    'components'          => [
        'authManager'  => null,
        'view'         => [
            'theme' => [
                'pathMap' => [
                    '@vendor/amnah/yii2-user/views' => '@franchise/views/user',
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
    ],
    'modules'             => [
        'user' => [
            'class'           => 'amnah\yii2\user\Module',
            'loginEmail'      => true,
            'requireEmail'    => true,
            'requireUsername' => false,
            'loginUsername'   => false,
            'controllerMap'   => [
                'default' => 'franchise\controllers\UserController',
            ],
            'modelClasses'    => [
                'User'         => 'common\models\User',
                'Profile'      => 'common\models\Profile',
                'Role'         => 'common\models\Role',
                'Organization' => 'common\models\Organization',
                'LoginForm'    => 'common\models\forms\LoginForm',
            ],
            'emailViewPath'   => '@franchise/mail',
        ],
    ],
    'params'              => $params,
];
