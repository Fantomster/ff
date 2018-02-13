<?php
return [
    'google-api' => [
        'key-id' => 'AIzaSyAiQcjJZXRr6xglrEo3yT_fFRn-TbLGj_M',
        'language'=>'ru-RU'
    ],
    'pictures' => [
        'org-noavatar' => 'https://s3-eu-west-1.amazonaws.com/static.f-keeper.ru/rest-noavatar.gif',
        'client-noavatar' => 'https://s3-eu-west-1.amazonaws.com/static.f-keeper.ru/restaurant-noavatar.gif',
        'vendor-noavatar' => 'https://s3-eu-west-1.amazonaws.com/static.f-keeper.ru/vendor-noavatar.gif',
        'bill-logo' => 'https://s3-eu-west-1.amazonaws.com/static.f-keeper.ru/logo-mix.png',
    ],
    'password_generation' => Yii::t('app', 'common.config.params.pass', ['ru'=>'Создание пароля для входа в систему MixCart']),
    'protocol' => 'http',
    'franchiseeHost' => '//partner.mixcart.ru',
    'integratAdminID' => [],

    #id франчази к которому крепим организации, для которых не нашли франчей
    'default_franchisee_id' => 1,
    
    //static urls
    'staticUrl' => [
        'market' => 'https://market.mixcart.test/',
        'franchise' => 'http://fr.mixcart.test/',
        'client' => 'https://client.mixcart.test/',
        'home' => 'https://mixcart.ru/',
        'about' => 'https://mixcart.ru/about.html',
        'contacts' => 'https://mixcart.ru/contacts.html',
    ],

    'enableYandexMetrics' => 1,
    /**
     * Массив ID организаций, у которых включено логирование ответов на запросы
     * Запись идет в файлы
     *  /runtime/logs/iiko_api_response_{ID}.log
     */
    'iikoLogOrganization' => []
];
