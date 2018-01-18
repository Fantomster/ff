<?php
/* @var $this \yii\web\View */
/* @var $content string */

use backend\assets\AppAsset;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use common\widgets\Alert;
use nirvana\showloading\ShowLoadingAsset;

ShowLoadingAsset::register($this);
$this->registerCss('#loader-show {position:absolute;width:100%;display:none;}');

AppAsset::register($this);

$customJs = <<< JS
$('#loader-show').css('height',$(window).height());
$(window).on('resize',function() {
    $('#loader-show').css('height',$(window).height());
});

$.post('/sms/ajax-balance',function(data){
    $('#sms-info').html(data);
});

JS;
$this->registerJs($customJs, yii\web\View::POS_READY);
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
                'brandUrl' => Yii::$app->homeUrl,
                'options' => [
                    'class' => 'navbar-inverse navbar-fixed-top',
                ],
            ]);
            $menuItems = [
                [
                    'label' => 'Статистика',
                    'items' => [
                        [
                            'label' => 'Регистрация',
                            'url' => ['/statistics/registered'],
                        ],
                        [
                            'label' => 'Заказы',
                            'url' => ['/statistics/orders'],
                        ],
                        [
                            'label' => 'Оборот',
                            'url' => ['/statistics/turnover'],
                        ],
                        [
                            'label' => 'Разное',
                            'url' => ['/statistics/misc'],
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
                                    'url' => ['/mp-category/index'],
                                ],
                            ],
                        ],
                        ['label' => 'SERVICEDESK',
                            'items' => [
                                [
                                    'label' => 'SERVICEDESK',
                                    'url' => ['/service-desk/index'],
                                    'linkOptions' => ['style' => 'color:#f4c871;font-size:bold']
                                ],
                                [
                                    'label' => 'СМС сообщения',
                                    'url' => ['/sms'],
                                ],
                                [
                                    'label' => 'Переводы СМС',
                                    'url' => ['/sms/message'],
                                ],
                                [
                                    'label' => 'Все переводы',
                                    'url' => ['/translations/message'],
                                ]
                            ],
                        ],
                        [
                            'label' => 'Пользователи',
                            'items' => [
                                [
                                    'label' => 'Общий список',
                                    'url' => ['/client/index'],
                                ],
                                [
                                    'label' => 'Менеджеры MixCart',
                                    'url' => ['/client/managers'],
                                ],
                            ],
                        ],
                        [
                            'label' => 'Организации',
                            'items' => [
                                [
                                    'label' => 'Общий список',
                                    'url' => ['/organization/index'],
                                ],
                                [
                                    'label' => 'Регионы доставки - поставщик',
                                    'url' => ['/delivery-regions/index'],
                                ],
                                [
                                    'label' => 'Одобренные для f-market',
                                    'url' => ['/buisiness-info/index'],
                                ],
                                [
                                    'label' => 'Франшиза',
                                    'url' => ['/franchisee/index'],
                                ],
                                [
                                    'label' => 'Заявки на регистрацию орг-ий',
                                    'url' => ['/agent-request/index'],
                                ],
                                [
                                    'label' => 'Платежи',
                                    'url' => ['/payment/index'],
                                ],
                            ],
                        ],
                        [
                            'label' => 'Заказы и заявки',
                            'items' => [
                                ['label' => 'Заказы', 'url' => ['/order/index']],
                                ['label' => 'Заявки', 'url' => ['/request/index']],
                            ],
                        ],
                        [
                            'label' => 'Товары',
                            'items' => [
                                [
                                    'label' => 'Общий список',
                                    'url' => ['/goods/index'],
                                ],
                                [
                                    'label' => 'Загруженные каталоги',
                                    'url' => ['/goods/uploaded-catalogs'],
                                ],
                            ],
                        ],
                    ]);

                  //  if ((Yii::$app->user->id === 467) || (Yii::$app->user->id === 3529) || (Yii::$app->user->id === 4435) || (Yii::$app->user->id === 7761)) {
                      if (in_array(Yii::$app->user->id,Yii::$app->params['integratAdminID'])) {
                        $menuItems = array_merge($menuItems, [
                            [
                                'label' => 'Интеграция',
                                'items' => [
                                    [
                                        'label' => 'Лицензии MixCart',
                                        'url' => ['/integration/index'],
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
                'options' => ['class' => 'navbar-nav navbar-right'],
                'items' => $menuItems,
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
                <p class="pull-left">&copy; MixCart <?= date('Y') ?></p>

                <p class="pull-right">Работает, оно работает!</p>

                
            </div>
        </footer>
        <div id="loader-show"></div>
        <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>
