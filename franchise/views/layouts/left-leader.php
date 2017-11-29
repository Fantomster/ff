<aside class="main-sidebar">

    <section class="sidebar">
        <?php if (!Yii::$app->user->isGuest) { ?>
            <?=
            dmstr\widgets\Menu::widget(
                    [
                        'options' => ['class' => 'sidebar-menu'],
                        'encodeLabels' => false,
                        'items' => [
                            ['label' => Yii::t('app', 'franchise.views.layouts.navi_three', ['ru'=>'НАВИГАЦИЯ']), 'options' => ['class' => 'header']],
                            ['label' => Yii::t('app', 'franchise.views.layouts.desktop_two', ['ru'=>'Рабочий стол']), 'icon' => 'home', 'url' => ['site/index']],
                            ['label' => Yii::t('app', 'franchise.views.layouts.rest_two', ['ru'=>'Рестораны']), 'icon' => 'cutlery', 'url' => ['organization/clients'], 'options' => ['class' => 'hidden-xs']],
                            ['label' => Yii::t('app', 'franchise.views.layouts.vendors_two', ['ru'=>'Поставщики']), 'icon' => 'users', 'url' => ['organization/vendors'], 'options' => ['class' => 'hidden-xs']],
                            ['label' => Yii::t('app', 'franchise.views.layouts.orders_two', ['ru'=>'Заказы']), 'icon' => 'history', 'url' => ['site/orders'], 'options' => ['class' => 'hidden-xs']],
                            ['label' => Yii::t('app', 'franchise.views.layouts.orders_three', ['ru'=>'Заявки']), 'icon' => 'paper-plane', 'url' => ['site/requests'], 'options' => ['class' => 'hidden-xs']],
                            ['label' => Yii::t('app', 'franchise.views.layouts.anal_two', ['ru'=>'Аналитика']), 'icon' => 'signal', 'url' => ['analytics/index'], 'options' => ['class' => 'hidden-xs']],
                            ['label' => Yii::t('app', 'franchise.views.layouts.promo', ['ru'=>'Промо']), 'icon' => 'gift', 'url' => ['site/promotion'], 'options' => ['class' => 'hidden-xs']],
                            ['label' => Yii::t('app', 'franchise.views.layouts.tech_supp', ['ru'=>'Тех. поддержка']), 'icon' => 'life-buoy', 'url' => ['site/service-desk'], 'options' => ['class' => 'hidden-xs']],
                            [
                                'label' => Yii::t('app', 'franchise.views.layouts.settings_two', ['ru'=>'Настройки']),
                                'icon' => 'gears',
                                'url' => '#', //['client/settings'],
                                'options' => ['class' => "treeview hidden-xs"],
                                'items' => [
                                    ['label' => Yii::t('app', 'franchise.views.layouts.custom_two', ['ru'=>'Общие']), 'icon' => 'circle-o', 'url' => ['site/settings']],
                                    ['label' => Yii::t('app', 'franchise.views.layouts.employees_two', ['ru'=>'Сотрудники']), 'icon' => 'circle-o', 'url' => ['site/users']],
                                ]
                            ],
                        ],
                    ]
            )
            ?>
<?php } ?>
    </section>

</aside>
