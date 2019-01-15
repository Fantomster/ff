<?php

$params = array_merge(
        require(__DIR__ . '/../../common/config/params.php'), require(__DIR__ . '/../../common/config/params-local.php'), require(__DIR__ . '/params.php'), require(__DIR__ . '/params-local.php')
);

return [
    'id'                  => 'app-console',
    'basePath'            => dirname(__DIR__),
    'bootstrap'           => ['log'],
    'controllerNamespace' => 'console\controllers',
    'controllerMap'       => [
        'watcher-daemon' => [
            'class'             => "\console\controllers\WatcherDaemonController",
            'maxChildProcesses' => 5,
            'isMultiInstance'   => false,
            'daemons'           => [
                ['className' => 'iikoLogDaemonController', 'enabled' => true],
                ['className' => 'TillypadLogDaemonController', 'enabled' => true],
            ]
        ],
        'abaddon-daemon' => [
            'class'             => "\console\controllers\AbaddonDaemonController",
            'maxChildProcesses' => 100,
            'isMultiInstance'   => false,
        ],
    ],
    'aliases'             => [
        '@baseUrl' => 'https://mixcart.ru'
    ],
    'params'              => $params,
];
