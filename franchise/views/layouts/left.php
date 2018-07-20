<aside class="main-sidebar">

    <section class="sidebar">
        <?php if (!Yii::$app->user->isGuest) { ?>
            <?=
            dmstr\widgets\Menu::widget(
                    [
                        'options' => ['class' => 'sidebar-menu tree', 'data-widget' => "tree"],
                        'encodeLabels' => false,
                        'items' => [
                            ['label' => Yii::t('app', 'franchise.views.layouts.navi', ['ru'=>'НАВИГАЦИЯ']), 'options' => ['class' => 'header']],
                            ['label' => Yii::t('app', 'franchise.views.layouts.desktop', ['ru'=>'Рабочий стол']), 'icon' => 'home', 'url' => ['site/index']],
                            ['label' => Yii::t('app', 'franchise.views.layouts.rest', ['ru'=>'Рестораны']), 'icon' => 'cutlery', 'url' => ['organization/clients'], 'options' => ['class' => 'hidden-xs']],
                            ['label' => Yii::t('app', 'franchise.views.layouts.vendors', ['ru'=>'Поставщики']), 'icon' => 'users', 'url' => ['organization/vendors'], 'options' => ['class' => 'hidden-xs']],
                            ['label' => Yii::t('app', 'franchise.views.layouts.orders', ['ru'=>'Заказы']), 'icon' => 'history', 'url' => ['site/orders'], 'options' => ['class' => 'hidden-xs']],
                            ['label' => Yii::t('app', 'franchise.views.layouts.requests', ['ru'=>'Заявки']), 'icon' => 'paper-plane', 'url' => ['site/requests'], 'options' => ['class' => 'hidden-xs']],
                            ['label' => Yii::t('app', 'franchise.views.layouts.anal', ['ru'=>'Аналитика']), 'icon' => 'signal', 'url' => ['analytics/index'], 'options' => ['class' => 'hidden-xs']],
                            ['label' => Yii::t('app', 'franchise.views.layouts.fin', ['ru'=>'Финансы']), 'icon' => 'money', 'url' => ['finance/index'], 'options' => ['class' => 'hidden-xs']],
                            ['label' => Yii::t('app', 'franchise.views.layouts.promo', ['ru'=>'Промо']), 'icon' => 'gift', 'url' => ['site/promotion'], 'options' => ['class' => 'hidden-xs']],
                            ['label' => Yii::t('app', 'franchise.views.layouts.tech', ['ru'=>'Тех. поддержка']), 'icon' => 'life-buoy', 'url' => ['site/service-desk'], 'options' => ['class' => 'hidden-xs']],
                            [
                                'label' => Yii::t('app', 'franchise.views.layouts.settings', ['ru'=>'Настройки']),
                                'icon' => 'gears',
                                'url' => '#', //['client/settings'],
                                'options' => ['class' => "treeview hidden-xs"],
                                'items' => [
                                    ['label' => Yii::t('app', 'franchise.views.layouts.custom', ['ru'=>'Общие']), 'icon' => 'circle-o', 'url' => ['site/settings']],
                                    ['label' => Yii::t('app', 'franchise.views.layouts.employees', ['ru'=>'Сотрудники']), 'icon' => 'circle-o', 'url' => ['site/users']],
                                ]
                            ],
                        ],
                    ]
            )
            ?>
<?php } ?>
    </section>

</aside>
