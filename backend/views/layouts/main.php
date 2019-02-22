<?php
/* @var $this \yii\web\View */

/* @var $content string */

use backend\assets\AppAsset;
use common\models\IntegrationSettingChange;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use common\widgets\Alert;
use nirvana\showloading\ShowLoadingAsset;

ShowLoadingAsset::register($this);
$this->registerCss('#loader-show {position:absolute;width:100%;display:none;} .nav > li > a {padding: 10px 8px;}');

AppAsset::register($this);

$url = \yii\helpers\Url::to(['/sms/ajax-balance']);

$customJs = <<< JS
$('#loader-show').css('height',$(window).height());
$(window).on('resize',function() {
    $('#loader-show').css('height',$(window).height());
});

$.post('$url',function(data){
    $('#sms-info').html(data);
});

JS;
$this->registerJs($customJs, yii\web\View::POS_READY);
$countSettingChange = IntegrationSettingChange::count();
$countSettingChangeHtml = '';
if ($countSettingChange > 0) {
    $countSettingChangeHtml = "<span class='badge' style='background-color: red;'>{$countSettingChange}</span>";
}
?>
<?php $this->beginPage() ?>

<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<div id="loader-show"></div>
<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => 'MixCart',
        'brandUrl'   => Yii::$app->homeUrl,
        'options'    => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
    $menuItems = [
        [
            'label' => 'Статистика',
            'items' => [
                [
                    'label' => 'Регистрация',
                    'url'   => ['/statistics/registered'],
                ],
                [
                    'label' => 'Заказы',
                    'url'   => ['/statistics/orders'],
                ],
                [
                    'label' => 'Оборот',
                    'url'   => ['/statistics/turnover'],
                ],
                [
                    'label' => 'Динамика',
                    'url'   => ['/statistics/dynamics'],
                ],
                [
                    'label' => 'Аналитика по Меркурию',
                    'url'   => ['/statistics/mercury'],
                ],
                [
                    'label' => 'Использование Меркурия за последний месяц',
                    'url'   => ['/statistics/merc-active-org'],
                ],
                [
                    'label' => 'Разное',
                    'url'   => ['/statistics/misc'],
                ],
                [
                    'label' => 'Расширенная отчетность',
                    'url'   => ['/statistics/extended-reports'],
                ],
            ],
        ],
    ];
    if (Yii::$app->user->isGuest) {
        $menuItems[] = ['label' => 'Login', 'url' => ['/user/login']];
    } else {
        if (Yii::$app->user->identity->role_id === \common\models\Role::ROLE_ADMIN) {
            $menuItems = array_merge($menuItems, [
                [
                    'label' => 'SEO',
                    'items' => [
                        [
                            'label' => 'Категории',
                            'url'   => ['/mp-category/index'],
                        ],
                    ],
                ],
                ['label' => 'SERVICEDESK',
                 'items' => [
                     [
                         'label'       => 'SERVICEDESK',
                         'url'         => ['/service-desk/index'],
                         'linkOptions' => ['style' => 'color:#f4c871;font-size:bold']
                     ],
                     [
                         'label' => 'СМС сообщения',
                         'url'   => ['/sms'],
                     ],
                     [
                         'label' => 'Переводы СМС',
                         'url'   => ['/sms/message'],
                     ],
                     [
                         'label' => 'Все переводы',
                         'url'   => ['/translations/message'],
                     ],
                     [
                         'label' => 'Поля для amoCRM',
                         'url'   => ['/amo/index'],
                     ],
                     [
                         'label' => 'Тестовая почта',
                         'url'   => ['/site/send-test-mail'],
                     ],
                     [
                         'label' => 'Оператор заказов',
                         'url'   => ['/order/operator'],
                     ],
                     [
                         'label' => 'Промо-акции',
                         'url'   => ['/promo-action/index'],
                     ],
                 ],
                ],
                [
                    'label' => 'Пользователи',
                    'items' => [
                        [
                            'label' => 'Общий список',
                            'url'   => ['/client/index'],
                        ],
                        [
                            'label' => 'Менеджеры MixCart',
                            'url'   => ['/client/managers'],
                        ],
                        [
                            'label' => 'Сотрудники поставщиков',
                            'url'   => ['/client/postavs'],
                        ],
                        [
                            'label' => 'Сотрудники ресторанов',
                            'url'   => ['/client/restors'],
                        ],
                        [
                            'label' => 'Роли',
                            'url'   => ['/rbac/role'],
                        ],
                        [
                            'label' => 'Разрешения',
                            'url'   => ['/rbac/permission'],
                        ],
                        [
                            'label' => 'Правила',
                            'url'   => ['/rbac/rule'],
                        ],
                        [
                            'label' => 'Маршруты',
                            'url'   => ['/rbac/route'],
                        ],
                    ],
                ],
                [
                    'label' => 'Организации',
                    'items' => [
                        [
                            'label' => 'Общий список',
                            'url'   => ['/organization/index'],
                        ],
                        [
                            'label'       => "Изменение настроек {$countSettingChangeHtml}",
                            'url'         => ['/setting-change/index'],
                            'linkOptions' => [
                                'style' => 'display: flex; justify-content: space-between; align-items: center;'
                            ],
                        ],
                        [
                            'label' => 'Регионы доставки - поставщик',
                            'url'   => ['/delivery-regions/index'],
                        ],
                        [
                            'label' => 'Одобренные для f-market',
                            'url'   => ['/buisiness-info/index'],
                        ],
                        [
                            'label' => 'Франшиза',
                            'url'   => ['/franchisee/index'],
                        ],
                        [
                            'label' => 'Заявки на регистрацию орг-ий',
                            'url'   => ['/agent-request/index'],
                        ],
                        [
                            'label' => 'Платежи',
                            'url'   => ['/payment/index'],
                        ],
                        [
                            'label' => 'Тестовые вендоры',
                            'url'   => ['/organization/test-vendors'],
                        ],
                        [
                            'label' => 'Лицензии',
                            'url'   => ['/organization/list-organizations-for-licenses'],
                        ],
                    ],
                ],
                [
                    'label' => 'Заказы и заявки',
                    'items' => [
                        ['label' => 'Заказы', 'url' => ['/order/index']],
                        ['label' => 'Заказы с прикрепленными файлами', 'url' => ['/order/with-attachments']],
                        ['label' => 'Заявки', 'url' => ['/request/index']],
                    ],
                ],
                [
                    'label' => 'Товары',
                    'items' => [
                        [
                            'label' => 'Общий список',
                            'url'   => ['/goods/index'],
                        ],
                        [
                            'label' => 'Загруженные каталоги',
                            'url'   => ['/goods/uploaded-catalogs'],
                        ],
                        [
                            'label' => 'Валюты',
                            'url'   => ['/currency/index'],
                        ],
                        [
                            'label' => 'Ставки налогов',
                            'url'   => ['/vats/index'],
                        ],
                    ],
                ],
            ]);
            if (in_array(Yii::$app->user->id, Yii::$app->params['operatorsReportAdminIDs'])) {
                $reportFast = [
                    'label' => 'Быстрый отчет по операторам',
                    'url'   => ['/service-desk/fast-operators-report'],
                ];
                $report = [
                    'label' => 'Отчет по операторам',
                    'url'   => ['/service-desk/operators-report'],
                ];
                $menuItems[2]['items'][] = $reportFast;
                $menuItems[2]['items'][] = $report;
            }
            //  if ((Yii::$app->user->id === 467) || (Yii::$app->user->id === 3529) || (Yii::$app->user->id === 4435) || (Yii::$app->user->id === 7761)) {
            if (in_array(Yii::$app->user->id, Yii::$app->params['integratAdminID'])) {
                $menuItems = array_merge($menuItems, [
                    [
                        'label' => 'Интеграция',
                        'items' => [
                            [
                                'label' => 'Лицензии MixCart',
                                'url'   => ['/integration/index'],
                            ],
                            [
                                'label' => 'Журнал',
                                'url'   => ['/journal/index'],
                            ],
                            //  [
                            //      'label' => 'Загруженные каталоги',
                            //      'url' => ['/goods/uploaded-catalogs'],
                            //  ],
                        ],
                    ],
                ]);
            }
        }
        $menuItems[] = '<li>'
            . Html::beginForm(['/site/logout'], 'post')
            . Html::submitButton(
                'Logout (' . Yii::$app->user->identity->email . ')', ['class' => 'btn btn-link']
            )
            . Html::endForm()
            . '</li>';
    }
    echo Nav::widget([
        'options'      => ['class' => 'navbar-nav navbar-left'],
        'items'        => $menuItems,
        'encodeLabels' => false,
    ]);
    NavBar::end();
    ?>

    <div class="container">
        <span id="sms-info"></span>
        <?=
        Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ])
        ?>
        <?= Alert::widget() ?>
        <?= $content ?>

    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">© 2016 - <?= date('Y') ?> MixCart</p>

        <p class="pull-right">Работает, оно работает!</p>


    </div>
</footer>
<div id="loader-show"></div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
