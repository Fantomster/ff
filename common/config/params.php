<?php

return [
    'google-api'            => [
        'key-id'   => 'AIzaSyAiQcjJZXRr6xglrEo3yT_fFRn-TbLGj_M',
        'language' => 'ru-RU'
    ],
    'pictures'              => [
        'org-noavatar'    => 'https://static.mixcart.ru/rest-noavatar.gif',
        'client-noavatar' => 'https://static.mixcart.ru/restaurant-noavatar.gif',
        'vendor-noavatar' => 'https://static.mixcart.ru/vendor-noavatar.gif',
        'bill-logo'       => 'https://static.mixcart.ru/logo-mix.png',
    ],
    'password_generation'   => Yii::t('app', 'common.config.params.pass', ['ru' => 'Создание пароля для входа в систему MixCart']),
    'protocol'              => 'http',
    'franchiseeHost'        => '//partner.mixcart.ru',
    'integratAdminID'       => [],
    'operatorsReportAdminIDs' => [85, 16, 8832],
    #id франчази к которому крепим организации, для которых не нашли франчей
    'default_franchisee_id' => 1,
    //static urls
    'shortHome'             => 'mixcart.ru',
    'staticUrl'             => [
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
    'enableYandexMetrics'   => 1,
    /**
     * Массив ID организаций, у которых включено логирование ответов на запросы
     * Запись идет в файлы
     *  /runtime/logs/iiko_api_response_{ID}.log
     */
    'iikoLogOrganization'   => [],

    'web'         => 'https://mixcart.ru/',
    /**
     * Логирование запросов к ВебАпи
     */
    'web_api_log' => true,

    'e_com' => [
        'login'       => 'markettest',
        'pass'        => 'e1fa52810ea9d18a5af901c147c804e6',
        'loginClient' => 'markettest1',
        'passClient'  => '32da77b28033f8fcd7d6d64a9801062d',
    ],

    'fireBase' => [
        'DEFAULT_URL'       => 'https://mixcart-test.firebaseio.com',
        'DEFAULT_TOKEN'     => '',
        'DEFAULT_PATH'      => '/',
        'apiKey'            => "AIzaSyCJU32Bx9BvEU2FLd0BS3FZw1fKTmLTc_M",
        'authDomain'        => "mixcart-test.firebaseapp.com",
        'projectId'         => "mixcart-test",
        'storageBucket'     => "mixcart-test.appspot.com",
        'messagingSenderId' => "1068392671931"
    ],

    'vtsHttp'              => [
        'authLink'       => 'https://t2-mercury.vetrf.ru/hs/',
        'vsdLink'        => 'https://t2-mercury.vetrf.ru/pub/operatorui?_language=ru&_action=showVetDocumentFormByUuid&uuid=',
        'pdfLink'        => 'https://t2-mercury.vetrf.ru/hs/operatorui?printType=1&preview=false&_action=printVetDocumentList&_language=ru&isplayPreview=false&displayRecipient=true&transactionPk=&vetDocument=&batchNumber=&printPk=',
        'chooseFirmLink' => 'https://t2-mercury.vetrf.ru/hs/operatorui?_action=chooseServicedFirm&_language=ru&firmGuid=',
    ],
    'edi_api_data' => [
        'edi_api_leradata_url' => 'https://leradata.pro/api/vetis/api.php',
        'edi_api_order_document_id' => 220,
        'edi_api_recadv_document_id' => 351
    ]
];
