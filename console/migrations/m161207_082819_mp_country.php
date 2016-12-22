<?php
use yii\db\Schema;
use yii\db\Migration;

class m161207_082819_mp_country extends Migration
{
    public function safeUp() {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%mp_country}}', [
            'id' => Schema::TYPE_PK,
            'name' => Schema::TYPE_STRING . '(100) NOT NULL',
            'full_name' => Schema::TYPE_STRING . '(150) NOT NULL',
            'en_name' => Schema::TYPE_STRING . '(150) NOT NULL',
            'alpha2' => Schema::TYPE_STRING . '(2) NOT NULL',
            'alpha3' => Schema::TYPE_STRING . '(3) NOT NULL',
            'location' => Schema::TYPE_STRING . '(100) NOT NULL'
            ], $tableOptions);
        $this->batchInsert('{{%mp_country}}', ['name', 'full_name', 'en_name', 'alpha2', 'alpha3', 'location'], [
            ['Абхазия','Республика Абхазия','Abkhazia','AB','ABH','Закавказье'],
            ['Австралия','','Australia','AU','AUS','Австралия и Новая Зеландия'],
            ['Австрия','Австрийская Республика','Austria','AT','AUT','Западная Европа'],
            ['Азербайджан','Республика Азербайджан','Azerbaijan','AZ','AZE','Западная Азия'],
            ['Албания','Республика Албания','Albania','AL','ALB','Южная Европа'],
            ['Алжир','Алжирская Народная Демократическая Республика','Algeria','DZ','DZA','Северная Африка'],
            ['Американское Самоа','','American Samoa','AS','ASM','Полинезия'],
            ['Ангилья','','Anguilla','AI','AIA','Карибский бассейн'],
            ['Ангола','Республика Ангола','Angola','AO','AGO','Центральная Африка'],
            ['Андорра','Княжество Андорра','Andorra','AD','AND','Южная Европа'],
            ['Антарктида','','Antarctica','AQ','ATA',''],
            ['Антигуа и Барбуда','','Antigua and Barbuda','AG','ATG','Карибский бассейн'],
            ['Аргентина','Аргентинская Республика','Argentina','AR','ARG','Южная Америка'],
            ['Армения','Республика Армения','Armenia','AM','ARM','Западная Азия'],
            ['Аруба','','Aruba','AW','ABW','Карибский бассейн'],
            ['Афганистан','Переходное Исламское Государство Афганистан','Afghanistan','AF','AFG','Южная часть Центральной Азии'],
            ['Багамы','Содружество Багамы','Bahamas','BS','BHS','Карибский бассейн'],
            ['Бангладеш','Народная Республика Бангладеш','Bangladesh','BD','BGD','Южная часть Центральной Азии'],
            ['Барбадос','','Barbados','BB','BRB','Карибский бассейн'],
            ['Бахрейн','Королевство Бахрейн','Bahrain','BH','BHR','Западная Азия'],
            ['Беларусь','Республика Беларусь','Belarus','BY','BLR','Восточная Европа'],
            ['Белиз','','Belize','BZ','BLZ','Карибский бассейн'],
            ['Бельгия','Королевство Бельгии','Belgium','BE','BEL','Западная Европа'],
            ['Бенин','Республика Бенин','Benin','BJ','BEN','Западная Африка'],
            ['Бермуды','','Bermuda','BM','BMU','Северная Америка'],
            ['Болгария','Республика Болгария','Bulgaria','BG','BGR','Восточная Европа'],
            ['Боливия, Многонациональное Государство','Многонациональное Государство Боливия','Bolivia, plurinational state of','BO','BOL','Южная Америка'],
            ['Бонайре, Саба и Синт-Эстатиус','','Bonaire, Sint Eustatius and Saba','BQ','BES','Карибский бассейн'],
            ['Босния и Герцеговина','','Bosnia and Herzegovina','BA','BIH','Южная Европа'],
            ['Ботсвана','Республика Ботсвана','Botswana','BW','BWA','Южная часть Африки'],
            ['Бразилия','Федеративная Республика Бразилия','Brazil','BR','BRA','Южная Америка'],
            ['Британская территория в Индийском океане','','British Indian Ocean Territory','IO','IOT','Индийский океан'],
            ['Бруней-Даруссалам','','Brunei Darussalam','BN','BRN','Юго-Восточная Азия'],
            ['Буркина-Фасо','','Burkina Faso','BF','BFA','Западная Африка'],
            ['Бурунди','Республика Бурунди','Burundi','BI','BDI','Восточная Африка'],
            ['Бутан','Королевство Бутан','Bhutan','BT','BTN','Южная часть Центральной Азии'],
            ['Вануату','Республика Вануату','Vanuatu','VU','VUT','Меланезия'],
            ['Венгрия','Венгерская Республика','Hungary','HU','HUN','Восточная Европа'],
            ['Венесуэла Боливарианская Республика','Боливарийская Республика Венесуэла','Venezuela','VE','VEN','Южная Америка'],
            ['Виргинские острова, Британские','Британские Виргинские острова','Virgin Islands, British','VG','VGB','Карибский бассейн'],
            ['Виргинские острова, США','Виргинские острова Соединенных Штатов','Virgin Islands, U.S.','VI','VIR','Карибский бассейн'],
            ['Вьетнам','Социалистическая Республика Вьетнам','Vietnam','VN','VNM','Юго-Восточная Азия'],
            ['Габон','Габонская Республика','Gabon','GA','GAB','Центральная Африка'],
            ['Гаити','Республика Гаити','Haiti','HT','HTI','Карибский бассейн'],
            ['Гайана','Республика Гайана','Guyana','GY','GUY','Южная Америка'],
            ['Гамбия','Республика Гамбия','Gambia','GM','GMB','Западная Африка'],
            ['Гана','Республика Гана','Ghana','GH','GHA','Западная Африка'],
            ['Гваделупа','','Guadeloupe','GP','GLP','Карибский бассейн'],
            ['Гватемала','Республика Гватемала','Guatemala','GT','GTM','Центральная Америка'],
            ['Гвинея','Гвинейская Республика','Guinea','GN','GIN','Западная Африка'],
            ['Гвинея-Бисау','Республика Гвинея-Бисау','Guinea-Bissau','GW','GNB','Западная Африка'],
            ['Германия','Федеративная Республика Германия','Germany','DE','DEU','Западная Европа'],
            ['Гернси','','Guernsey','GG','GGY','Северная Европа'],
            ['Гибралтар','','Gibraltar','GI','GIB','Южная Европа'],
            ['Гондурас','Республика Гондурас','Honduras','HN','HND','Центральная Америка'],
            ['Гонконг','Специальный административный регион Китая Гонконг','Hong Kong','HK','HKG','Восточная Азия'],
            ['Гренада','','Grenada','GD','GRD','Карибский бассейн'],
            ['Гренландия','','Greenland','GL','GRL','Северная Америка'],
            ['Греция','Греческая Республика','Greece','GR','GRC','Южная Европа'],
            ['Грузия','','Georgia','GE','GEO','Западная Азия'],
            ['Гуам','','Guam','GU','GUM','Микронезия'],
            ['Дания','Королевство Дания','Denmark','DK','DNK','Северная Европа'],
            ['Джерси','','Jersey','JE','JEY','Северная Европа'],
            ['Джибути','Республика Джибути','Djibouti','DJ','DJI','Восточная Африка'],
            ['Доминика','Содружество Доминики','Dominica','DM','DMA','Карибский бассейн'],
            ['Доминиканская Республика','','Dominican Republic','DO','DOM','Карибский бассейн'],
            ['Египет','Арабская Республика Египет','Egypt','EG','EGY','Северная Африка'],
            ['Замбия','Республика Замбия','Zambia','ZM','ZMB','Восточная Африка'],
            ['Западная Сахара','','Western Sahara','EH','ESH','Северная Африка'],
            ['Зимбабве','Республика Зимбабве','Zimbabwe','ZW','ZWE','Восточная Африка'],
            ['Израиль','Государство Израиль','Israel','IL','ISR','Западная Азия'],
            ['Индия','Республика Индия','India','IN','IND','Южная часть Центральной Азии'],
            ['Индонезия','Республика Индонезия','Indonesia','ID','IDN','Юго-Восточная Азия'],
            ['Иордания','Иорданское Хашимитское Королевство','Jordan','JO','JOR','Западная Азия'],
            ['Ирак','Республика Ирак','Iraq','IQ','IRQ','Западная Азия'],
            ['Иран, Исламская Республика','Исламская Республика Иран','Iran, Islamic Republic of','IR','IRN','Южная часть Центральной Азии'],
            ['Ирландия','','Ireland','IE','IRL','Северная Европа'],
            ['Исландия','Республика Исландия','Iceland','IS','ISL','Северная Европа'],
            ['Испания','Королевство Испания','Spain','ES','ESP','Южная Европа'],
            ['Италия','Итальянская Республика','Italy','IT','ITA','Южная Европа'],
            ['Йемен','Йеменская Республика','Yemen','YE','YEM','Западная Азия'],
            ['Кабо-Верде','Республика Кабо-Верде','Cape Verde','CV','CPV','Западная Африка'],
            ['Казахстан','Республика Казахстан','Kazakhstan','KZ','KAZ','Южная часть Центральной Азии'],
            ['Камбоджа','Королевство Камбоджа','Cambodia','KH','KHM','Юго-Восточная Азия'],
            ['Камерун','Республика Камерун','Cameroon','CM','CMR','Центральная Африка'],
            ['Канада','','Canada','CA','CAN','Северная Америка'],
            ['Катар','Государство Катар','Qatar','QA','QAT','Западная Азия'],
            ['Кения','Республика Кения','Kenya','KE','KEN','Восточная Африка'],
            ['Кипр','Республика Кипр','Cyprus','CY','CYP','Западная Азия'],
            ['Киргизия','Киргизская Республика','Kyrgyzstan','KG','KGZ','Южная часть Центральной Азии'],
            ['Кирибати','Республика Кирибати','Kiribati','KI','KIR','Микронезия'],
            ['Китай','Китайская Народная Республика','China','CN','CHN','Восточная Азия'],
            ['Кокосовые (Килинг) острова','','Cocos (Keeling) Islands','CC','CCK','Индийский океан'],
            ['Колумбия','Республика Колумбия','Colombia','CO','COL','Южная Америка'],
            ['Коморы','Союз Коморы','Comoros','KM','COM','Восточная Африка'],
            ['Конго','Республика Конго','Congo','CG','COG','Центральная Африка'],
            ['Конго, Демократическая Республика','Демократическая Республика Конго','Congo, Democratic Republic of the','CD','COD','Центральная Африка'],
            ['Корея, Народно-Демократическая Республика','Корейская Народно-Демократическая Республика','Korea, Democratic People`s republic of','KP','PRK','Восточная Азия'],
            ['Корея, Республика','Республика Корея','Korea, Republic of','KR','KOR','Восточная Азия'],
            ['Коста-Рика','Республика Коста-Рика','Costa Rica','CR','CRI','Центральная Америка'],
            ['Кот д`Ивуар','Республика Кот д`Ивуар','Cote d`Ivoire','CI','CIV','Западная Африка'],
            ['Куба','Республика Куба','Cuba','CU','CUB','Карибский бассейн'],
            ['Кувейт','Государство Кувейт','Kuwait','KW','KWT','Западная Азия'],
            ['Кюрасао','','Curaçao','CW','CUW','Карибский бассейн'],
            ['Лаос','Лаосская Народно-Демократическая Республика','Lao People`s Democratic Republic','LA','LAO','Юго-Восточная Азия'],
            ['Латвия','Латвийская Республика','Latvia','LV','LVA','Северная Европа'],
            ['Лесото','Королевство Лесото','Lesotho','LS','LSO','Южная часть Африки'],
            ['Ливан','Ливанская Республика','Lebanon','LB','LBN','Западная Азия'],
            ['Ливийская Арабская Джамахирия','Социалистическая Народная Ливийская Арабская Джамахирия','Libyan Arab Jamahiriya','LY','LBY','Северная Африка'],
            ['Либерия','Республика Либерия','Liberia','LR','LBR','Западная Африка'],
            ['Лихтенштейн','Княжество Лихтенштейн','Liechtenstein','LI','LIE','Западная Европа'],
            ['Литва','Литовская Республика','Lithuania','LT','LTU','Северная Европа'],
            ['Люксембург','Великое Герцогство Люксембург','Luxembourg','LU','LUX','Западная Европа'],
            ['Маврикий','Республика Маврикий','Mauritius','MU','MUS','Восточная Африка'],
            ['Мавритания','Исламская Республика Мавритания','Mauritania','MR','MRT','Западная Африка'],
            ['Мадагаскар','Республика Мадагаскар','Madagascar','MG','MDG','Восточная Африка'],
            ['Майотта','','Mayotte','YT','MYT','Южная часть Африки'],
            ['Макао','Специальный административный регион Китая Макао','Macao','MO','MAC','Восточная Азия'],
            ['Малави','Республика Малави','Malawi','MW','MWI','Восточная Африка'],
            ['Малайзия','','Malaysia','MY','MYS','Юго-Восточная Азия'],
            ['Мали','Республика Мали','Mali','ML','MLI','Западная Африка'],
            ['Малые Тихоокеанские отдаленные острова Соединенных Штатов','','United States Minor Outlying Islands','UM','UMI','Индийский океан'],
            ['Мальдивы','Мальдивская Республика','Maldives','MV','MDV','Южная часть Центральной Азии'],
            ['Мальта','Республика Мальта','Malta','MT','MLT','Южная Европа'],
            ['Марокко','Королевство Марокко','Morocco','MA','MAR','Северная Африка'],
            ['Мартиника','','Martinique','MQ','MTQ','Карибский бассейн'],
            ['Маршалловы острова','Республика Маршалловы острова','Marshall Islands','MH','MHL','Микронезия'],
            ['Мексика','Мексиканские Соединенные Штаты','Mexico','MX','MEX','Центральная Америка'],
            ['Микронезия, Федеративные Штаты','Федеративные штаты Микронезии','Micronesia, Federated States of','FM','FSM','Микронезия'],
            ['Мозамбик','Республика Мозамбик','Mozambique','MZ','MOZ','Восточная Африка'],
            ['Молдова, Республика','Республика Молдова','Moldova','MD','MDA','Восточная Европа'],
            ['Монако','Княжество Монако','Monaco','MC','MCO','Западная Европа'],
            ['Монголия','','Mongolia','MN','MNG','Восточная Азия'],
            ['Монтсеррат','','Montserrat','MS','MSR','Карибский бассейн'],
            ['Мьянма','Союз Мьянма','Myanmar','MM','MMR','Юго-Восточная Азия'],
            ['Намибия','Республика Намибия','Namibia','NA','NAM','Южная часть Африки'],
            ['Науру','Республика Науру','Nauru','NR','NRU','Микронезия'],
            ['Непал','Федеративная Демократическая Республика Непал','Nepal','NP','NPL','Южная часть Центральной Азии'],
            ['Нигер','Республика Нигер','Niger','NE','NER','Западная Африка'],
            ['Нигерия','Федеративная Республика Нигерия','Nigeria','NG','NGA','Западная Африка'],
            ['Нидерланды','Королевство Нидерландов','Netherlands','NL','NLD','Западная Европа'],
            ['Никарагуа','Республика Никарагуа','Nicaragua','NI','NIC','Центральная Америка'],
            ['Ниуэ','Республика Ниуэ','Niue','NU','NIU','Полинезия'],
            ['Новая Зеландия','','New Zealand','NZ','NZL','Австралия и Новая Зеландия'],
            ['Новая Каледония','','New Caledonia','NC','NCL','Меланезия'],
            ['Норвегия','Королевство Норвегия','Norway','NO','NOR','Северная Европа'],
            ['Объединенные Арабские Эмираты','','United Arab Emirates','AE','ARE','Западная Азия'],
            ['Оман','Султанат Оман','Oman','OM','OMN','Западная Азия'],
            ['Остров Буве','','Bouvet Island','BV','BVT','Южный океан'],
            ['Остров Мэн','','Isle of Man','IM','IMN','Северная Европа'],
            ['Остров Норфолк','','Norfolk Island','NF','NFK','Австралия и Новая Зеландия'],
            ['Остров Рождества','','Christmas Island','CX','CXR','Индийский океан'],
            ['Остров Херд и острова Макдональд','','Heard Island and McDonald Islands','HM','HMD','Индийский океан'],
            ['Острова Кайман','','Cayman Islands','KY','CYM','Карибский бассейн'],
            ['Острова Кука','','Cook Islands','CK','COK','Полинезия'],
            ['Острова Теркс и Кайкос','','Turks and Caicos Islands','TC','TCA','Карибский бассейн'],
            ['Пакистан','Исламская Республика Пакистан','Pakistan','PK','PAK','Южная часть Центральной Азии'],
            ['Палау','Республика Палау','Palau','PW','PLW','Микронезия'],
            ['Палестинская территория, оккупированная','Оккупированная Палестинская территория','Palestinian Territory, Occupied','PS','PSE','Западная Азия'],
            ['Панама','Республика Панама','Panama','PA','PAN','Центральная Америка'],
            ['Папский Престол (Государство — город Ватикан)','','Holy See (Vatican City State)','VA','VAT','Южная Европа'],
            ['Папуа-Новая Гвинея','','Papua New Guinea','PG','PNG','Меланезия'],
            ['Парагвай','Республика Парагвай','Paraguay','PY','PRY','Южная Америка'],
            ['Перу','Республика Перу','Peru','PE','PER','Южная Америка'],
            ['Питкерн','','Pitcairn','PN','PCN','Полинезия'],
            ['Польша','Республика Польша','Poland','PL','POL','Восточная Европа'],
            ['Португалия','Португальская Республика','Portugal','PT','PRT','Южная Европа'],
            ['Пуэрто-Рико','','Puerto Rico','PR','PRI','Карибский бассейн'],
            ['Республика Македония','','Macedonia, The Former Yugoslav Republic Of','MK','MKD','Южная Европа'],
            ['Реюньон','','Reunion','RE','REU','Восточная Африка'],
            ['Россия','Российская Федерация','Russian Federation','RU','RUS','Восточная Европа'],
            ['Руанда','Руандийская Республика','Rwanda','RW','RWA','Восточная Африка'],
            ['Румыния','','Romania','RO','ROU','Восточная Европа'],
            ['Самоа','Независимое Государство Самоа','Samoa','WS','WSM','Полинезия'],
            ['Сан-Марино','Республика Сан-Марино','San Marino','SM','SMR','Южная Европа'],
            ['Сан-Томе и Принсипи','Демократическая Республика Сан-Томе и Принсипи','Sao Tome and Principe','ST','STP','Центральная Африка'],
            ['Саудовская Аравия','Королевство Саудовская Аравия','Saudi Arabia','SA','SAU','Западная Азия'],
            ['Свазиленд','Королевство Свазиленд','Swaziland','SZ','SWZ','Южная часть Африки'],
            ['Святая Елена, Остров вознесения, Тристан-да-Кунья','','Saint Helena, Ascension And Tristan Da Cunha','SH','SHN','Западная Африка'],
            ['Северные Марианские острова','Содружество Северных Марианских островов','Northern Mariana Islands','MP','MNP','Микронезия'],
            ['Сен-Бартельми','','Saint Barthélemy','BL','BLM','Карибский бассейн'],
            ['Сен-Мартен','','Saint Martin (French Part)','MF','MAF','Карибский бассейн'],
            ['Сенегал','Республика Сенегал','Senegal','SN','SEN','Западная Африка'],
            ['Сент-Винсент и Гренадины','','Saint Vincent and the Grenadines','VC','VCT','Карибский бассейн'],
            ['Сент-Люсия','','Saint Lucia','LC','LCA','Карибский бассейн'],
            ['Сент-Китс и Невис','','Saint Kitts and Nevis','KN','KNA','Карибский бассейн'],
            ['Сент-Пьер и Микелон','','Saint Pierre and Miquelon','PM','SPM','Северная Америка'],
            ['Сербия','Республика Сербия','Serbia','RS','SRB','Южная Европа'],
            ['Сейшелы','Республика Сейшелы','Seychelles','SC','SYC','Восточная Африка'],
            ['Сингапур','Республика Сингапур','Singapore','SG','SGP','Юго-Восточная Азия'],
            ['Синт-Мартен','','Sint Maarten','SX','SXM','Карибский бассейн'],
            ['Сирийская Арабская Республика','','Syrian Arab Republic','SY','SYR','Западная Азия'],
            ['Словакия','Словацкая Республика','Slovakia','SK','SVK','Восточная Европа'],
            ['Словения','Республика Словения','Slovenia','SI','SVN','Южная Европа'],
            ['Соединенное Королевство','Соединенное Королевство Великобритании и Северной Ирландии','United Kingdom','GB','GBR','Северная Европа'],
            ['Соединенные Штаты','Соединенные Штаты Америки','United States','US','USA','Северная Америка'],
            ['Соломоновы острова','','Solomon Islands','SB','SLB','Меланезия'],
            ['Сомали','Сомалийская Республика','Somalia','SO','SOM','Восточная Африка'],
            ['Судан','Республика Судан','Sudan','SD','SDN','Северная Африка'],
            ['Суринам','Республика Суринам','Suriname','SR','SUR','Южная Америка'],
            ['Сьерра-Леоне','Республика Сьерра-Леоне','Sierra Leone','SL','SLE','Западная Африка'],
            ['Таджикистан','Республика Таджикистан','Tajikistan','TJ','TJK','Южная часть Центральной Азии'],
            ['Таиланд','Королевство Таиланд','Thailand','TH','THA','Юго-Восточная Азия'],
            ['Тайвань (Китай)','','Taiwan, Province of China','TW','TWN','Восточная Азия'],
            ['Танзания, Объединенная Республика','Объединенная Республика Танзания','Tanzania, United Republic Of','TZ','TZA','Восточная Африка'],
            ['Тимор-Лесте','Демократическая Республика Тимор-Лесте','Timor-Leste','TL','TLS','Юго-Восточная Азия'],
            ['Того','Тоголезская Республика','Togo','TG','TGO','Западная Африка'],
            ['Токелау','','Tokelau','TK','TKL','Полинезия'],
            ['Тонга','Королевство Тонга','Tonga','TO','TON','Полинезия'],
            ['Тринидад и Тобаго','Республика Тринидад и Тобаго','Trinidad and Tobago','TT','TTO','Карибский бассейн'],
            ['Тувалу','','Tuvalu','TV','TUV','Полинезия'],
            ['Тунис','Тунисская Республика','Tunisia','TN','TUN','Северная Африка'],
            ['Туркмения','Туркменистан','Turkmenistan','TM','TKM','Южная часть Центральной Азии'],
            ['Турция','Турецкая Республика','Turkey','TR','TUR','Западная Азия'],
            ['Уганда','Республика Уганда','Uganda','UG','UGA','Восточная Африка'],
            ['Узбекистан','Республика Узбекистан','Uzbekistan','UZ','UZB','Южная часть Центральной Азии'],
            ['Украина','','Ukraine','UA','UKR','Восточная Европа'],
            ['Уоллис и Футуна','','Wallis and Futuna','WF','WLF','Полинезия'],
            ['Уругвай','Восточная Республика Уругвай','Uruguay','UY','URY','Южная Америка'],
            ['Фарерские острова','','Faroe Islands','FO','FRO','Северная Европа'],
            ['Фиджи','Республика островов Фиджи','Fiji','FJ','FJI','Меланезия'],
            ['Филиппины','Республика Филиппины','Philippines','PH','PHL','Юго-Восточная Азия'],
            ['Финляндия','Финляндская Республика','Finland','FI','FIN','Северная Европа'],
            ['Фолклендские острова (Мальвинские)','','Falkland Islands (Malvinas)','FK','FLK','Южная Америка'],
            ['Франция','Французская Республика','France','FR','FRA','Западная Европа'],
            ['Французская Гвиана','','French Guiana','GF','GUF','Южная Америка'],
            ['Французская Полинезия','','French Polynesia','PF','PYF','Полинезия'],
            ['Французские Южные территории','','French Southern Territories','TF','ATF','Индийский океан'],
            ['Хорватия','Республика Хорватия','Croatia','HR','HRV','Южная Европа'],
            ['Центрально-Африканская Республика','','Central African Republic','CF','CAF','Центральная Африка'],
            ['Чад','Республика Чад','Chad','TD','TCD','Центральная Африка'],
            ['Черногория','Республика Черногория','Montenegro','ME','MNE','Южная Европа'],
            ['Чешская Республика','','Czech Republic','CZ','CZE','Восточная Европа'],
            ['Чили','Республика Чили','Chile','CL','CHL','Южная Америка'],
            ['Швейцария','Швейцарская Конфедерация','Switzerland','CH','CHE','Западная Европа'],
            ['Швеция','Королевство Швеция','Sweden','SE','SWE','Северная Европа'],
            ['Шпицберген и Ян Майен','','Svalbard and Jan Mayen','SJ','SJM','Северная Европа'],
            ['Шри-Ланка','Демократическая Социалистическая Республика Шри-Ланка','Sri Lanka','LK','LKA','Южная часть Центральной Азии'],
            ['Эквадор','Республика Эквадор','Ecuador','EC','ECU','Южная Америка'],
            ['Экваториальная Гвинея','Республика Экваториальная Гвинея','Equatorial Guinea','GQ','GNQ','Центральная Африка'],
            ['Эландские острова','','Åland Islands','AX','ALA','Северная Европа'],
            ['Эль-Сальвадор','Республика Эль-Сальвадор','El Salvador','SV','SLV','Центральная Америка'],
            ['Эритрея','','Eritrea','ER','ERI','Восточная Африка'],
            ['Эстония','Эстонская Республика','Estonia','EE','EST','Северная Европа'],
            ['Эфиопия','Федеративная Демократическая Республика Эфиопия','Ethiopia','ET','ETH','Восточная Африка'],
            ['Южная Африка','Южно-Африканская Республика','South Africa','ZA','ZAF','Южная часть Африки'],
            ['Южная Джорджия и Южные Сандвичевы острова','','South Georgia and the South Sandwich Islands','GS','SGS','Южный океан'],
            ['Южная Осетия','Республика Южная Осетия','South Ossetia','OS','OST','Закавказье'],
            ['Южный Судан','','South Sudan','SS','SSD','Северная Африка'],
            ['Ямайка','','Jamaica','JM','JAM','Карибский бассейн'],
            ['Япония','','Japan','JP','JPN','Восточная Азия']
        ]);
    }
    public function safeDown() {
        $this->dropTable('{{%mp_country}}');
    }
}