<?php
return [
    'adminEmail' => 'noreply@mixcart.ru',
    'notificationsUrl' => 'https://notifications.f-keeper.ru:443',
    'maindUrl' => 'http://testama.f-keeper.ru',
    'licenseManagerPhone' => '8(499)404-10-18',
    /**
     * Методы которые не требуют авторизации в АПИ
     */
    'allow_methods' => [
        '/user/registration',
        '/user/registration-confirm',
        '/user/registration-repeat-sms',
        '/user/password-recovery',
        '/user/get-agreement',
        '/user/change-unconfirmed-users-phone',
        '/market/product',
        '/market/products',
        '/market/categories',
        '/market/organizations',
        '/payment/currency-list',
        '/system/datetime'
    ],
    'allow_methods_without_license' => [
        '/user/login',
        '/user/get-available-businesses',
        '/user/set-organization'
    ],
    'api_web_url' => 'https://api-dev.mixcart.ru'
];
