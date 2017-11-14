<aside class="main-sidebar">

    <section class="sidebar">
        <?php if (!Yii::$app->user->isGuest) { ?>
            <?=
            dmstr\widgets\Menu::widget(
                    [
                        'options' => ['class' => 'sidebar-menu tree', 'data-widget' => "tree"],
                        'encodeLabels' => false,
                        'items' => [
                            ['label' => Yii::t('app', 'НАВИГАЦИЯ'), 'options' => ['class' => 'header']],
                            ['label' => Yii::t('app', 'Рабочий стол'), 'icon' => 'home', 'url' => ['site/index']],
                            ['label' => Yii::t('app', 'Рестораны'), 'icon' => 'cutlery', 'url' => ['organization/clients'], 'options' => ['class' => 'hidden-xs']],
                            ['label' => Yii::t('app', 'Поставщики'), 'icon' => 'users', 'url' => ['organization/vendors'], 'options' => ['class' => 'hidden-xs']],
                            ['label' => Yii::t('app', 'Заказы'), 'icon' => 'history', 'url' => ['site/orders'], 'options' => ['class' => 'hidden-xs']],
                            ['label' => Yii::t('app', 'Заявки'), 'icon' => 'paper-plane', 'url' => ['site/requests'], 'options' => ['class' => 'hidden-xs']],
                            ['label' => Yii::t('app', 'Аналитика'), 'icon' => 'signal', 'url' => ['analytics/index'], 'options' => ['class' => 'hidden-xs']],
                            ['label' => Yii::t('app', 'Финансы'), 'icon' => 'money', 'url' => ['finance/index'], 'options' => ['class' => 'hidden-xs']],
                            ['label' => Yii::t('app', 'Промо'), 'icon' => 'gift', 'url' => ['site/promotion'], 'options' => ['class' => 'hidden-xs']],
                            ['label' => Yii::t('app', 'Тех. поддержка'), 'icon' => 'life-buoy', 'url' => ['site/service-desk'], 'options' => ['class' => 'hidden-xs']],
                            [
                                'label' => Yii::t('app', 'Настройки'),
                                'icon' => 'gears',
                                'url' => '#', //['client/settings'],
                                'options' => ['class' => "treeview hidden-xs"],
                                'items' => [
                                    ['label' => Yii::t('app', 'Общие'), 'icon' => 'circle-o', 'url' => ['site/settings']],
                                    ['label' => Yii::t('app', 'Сотрудники'), 'icon' => 'circle-o', 'url' => ['site/users']],
                                ]
                            ],
                        ],
                    ]
            )
            ?>
<?php } ?>
    </section>

</aside>
