<?php
return [
    'adminEmail' => 'noreply@mixcart.ru',
    'notificationsUrl' => 'https://notifications.f-keeper.ru:443',
    'maindUrl' => 'http://testama.f-keeper.ru',

    /**
     * Методы которые не требуют авторизации в АПИ
     */
    'allow_methods' => [
        '/user/registration',
        '/user/registration-confirm',
        '/user/registration-repeat-sms',
        '/user/password-recovery',
        '/market/product',
        '/market/products',
        '/market/categories',
        '/market/organizations',
        '/payment/currency-list'
    ]
];
