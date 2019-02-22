<?php

$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'), require(__DIR__ . '/../../common/config/params-local.php'), require(__DIR__ . '/params.php'), require(__DIR__ . '/params-local.php')
);

return [
    'id'                  => 'app-backend',
    'basePath'            => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap'           => ['log'],
    'modules'             => [
        'rbac' => [
            'class' => 'backend\modules\rbac\RbacModule',
        ],
    ],
    'components'          => [
        'request'      => [
            'csrfParam' => '_csrf-fk',
        ],
        'session'      => [
            // this is the name of the session cookie used for login on the backend
            'name' => 'FKEEPSESSID',
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
    ],
    'params'              => $params,
];
