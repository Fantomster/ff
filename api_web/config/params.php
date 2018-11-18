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
        '/user/set-organization',
        '/user/organization',
        '/payment/currency-list',
        '/market/products',
        '/order/info',
        '/order/info-by-unconfirmed-vendor',
        '/order/update-order-by-unconfirmed-vendor',
        '/order/products-list-for-unconfirmed-vendor',
        '/order/categories-for-unconfirmed-vendor',
        '/order/cancel-order-by-unconfirmed-vendor',
        '/order/complete-order-by-unconfirmed-vendor'
    ],
    'api_web_url' => 'https://api-dev.mixcart.ru',
    'staticUrl' => [
        'ru' => [
            'market'    => 'https://market.mixcart.ru/',
            'franchise' => 'http://fr.mixcart.ru/',
            'client'    => 'https://client.mixcart.ru/',
            'home'      => 'https://mixcart.ru/',
            'about'     => 'https://mixcart.ru/about.html',
            'contacts'  => 'https://mixcart.ru/contacts.html',
        ],
        'en' => [
            'market'    => 'https://market.mixcart.ru/',
            'franchise' => 'http://fr.mixcart.ru/',
            'client'    => 'https://client.mixcart.ru/',
            'home'      => 'https://mixcart.ru/',
            'about'     => 'https://mixcart.ru/about.html',
            'contacts'  => 'https://mixcart.ru/contacts.html',
        ],
        'es' => [
            'market'    => 'https://market.mixcart.ru/es/',
            'franchise' => 'http://fr.mixcart.ru/',
            'client'    => 'https://client.mixcart.ru/',
            'home'      => 'https://mixcart.ru/es/',
            'about'     => 'https://mixcart.ru/es/about.html',
            'contacts'  => 'https://mixcart.ru/es/contacts.html',
        ],
        'md' => [
            'market'    => 'https://market.mixcart.ru/md/',
            'franchise' => 'http://fr.mixcart.ru/',
            'client'    => 'https://client.mixcart.ru/',
            'home'      => 'https://mixcart.ru/md/',
            'about'     => 'https://mixcart.ru/md/about.html',
            'contacts'  => 'https://mixcart.ru/md/contacts.html',
        ],
        'ua' => [
            'market'    => 'https://market.mixcart.ru/ua/',
            'franchise' => 'http://fr.mixcart.ru/',
            'client'    => 'https://client.mixcart.ru/',
            'home'      => 'https://mixcart.ru/ua/',
            'about'     => 'https://mixcart.ru/ua/about.html',
            'contacts'  => 'https://mixcart.ru/ua/contacts.html',
        ],
    ],
];
