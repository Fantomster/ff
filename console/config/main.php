<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'console\controllers',
    'components' => [
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'urlManager' => [
            'baseUrl' => 'http://example.com/'
        ]
    ],
    'controllerMap' => [
        'command-bus' => [
            'class' => 'trntv\bus\console\BackgroundBusController',
        ],
        /*'pusher-daemon' => [
            'class' => 'console\modules\servers\daemons\PusherDaemonController',
            'demonize' => false,
            'isMultiInstance' => false,
            'maxChildProcesses' => 1,
        ],*/
        'deduction-daemon' => [
            'class' => 'console\modules\daemons\controllers\DeductionDaemonController',
            'demonize' => false,
            'isMultiInstance' => true,
            'maxChildProcesses' => 3,
            'host' => '192.168.10.142',    #host - имя хоста, на котором запущен сервер RabbitMQ
            'port' => 5672,        #port - номер порта сервиса, по умолчанию - 5672
            'user' => 'mvps',        #user - имя пользователя для соединения с сервером
            'password' => 'Pfobnf55',        #password
            'queue' => 'ductions',
        ],
        /*'checker-daemon' => [
            'class' => 'console\modules\servers\daemons\CheckerDaemonController',
            'demonize' => false,
            'isMultiInstance' => true,
            'maxChildProcesses' => 3,
        ],*/
        'watcher-daemon' => [
            'class' => 'console\controllers\WatcherDaemonController',
            //'daemonFolder' => 'servers',
            'daemons' => [
                //['className' => 'PusherDaemonController', 'enabled' => true],
                //['className' => 'CheckerDaemonController', 'enabled' => true],
                ['className' => 'FamworkDaemonController', 'enabled' => true]

            ]
        ],
    ],
    'aliases' => [
          '@baseUrl'=>'https://mixcart.ru'
//        '@web' => 'http://f-keeper.dev',
//        '@webroot' => '/var/www/html/f-keeper.dev/frontend/web',
    ],
    'params' => $params,
];
