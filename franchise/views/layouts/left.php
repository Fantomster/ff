<aside class="main-sidebar">

    <section class="sidebar">
        <?php if (!Yii::$app->user->isGuest) { ?>
            <?=
            dmstr\widgets\Menu::widget(
                    [
                        'options' => ['class' => 'sidebar-menu'],
                        'encodeLabels' => false,
                        'items' => [
                            ['label' => 'НАВИГАЦИЯ', 'options' => ['class' => 'header']],
                            ['label' => 'Рабочий стол', 'icon' => 'home', 'url' => ['site/index']],
                            ['label' => 'Рестораны', 'icon' => 'cutlery', 'url' => ['organization/clients'], 'options' => ['class' => 'hidden-xs']],
                            ['label' => 'Поставщики', 'icon' => 'users', 'url' => ['organization/vendors'], 'options' => ['class' => 'hidden-xs']],
                            ['label' => 'Заказы', 'icon' => 'history', 'url' => ['site/orders'], 'options' => ['class' => 'hidden-xs']],
                            ['label' => 'Заявки', 'icon' => 'paper-plane', 'url' => ['site/requests'], 'options' => ['class' => 'hidden-xs']],
                            ['label' => 'Аналитика', 'icon' => 'signal', 'url' => ['analytics/index'], 'options' => ['class' => 'hidden-xs']],
                            ['label' => 'Финансы', 'icon' => 'money', 'url' => ['finance/index'], 'options' => ['class' => 'hidden-xs']],
                            ['label' => 'Промо', 'icon' => 'gift', 'url' => ['site/promotion'], 'options' => ['class' => 'hidden-xs']],
                            ['label' => 'Тех. поддержка', 'icon' => 'life-buoy', 'url' => ['site/service-desk'], 'options' => ['class' => 'hidden-xs']],
                            [
                                'label' => 'Настройки',
                                'icon' => 'gears',
                                'url' => '#', //['client/settings'],
                                'options' => ['class' => "treeview hidden-xs"],
                                'items' => [
                                    ['label' => 'Общие', 'icon' => 'circle-o', 'url' => ['site/settings']],
                                    ['label' => 'Сотрудники', 'icon' => 'circle-o', 'url' => ['site/users']],
                                ]
                            ],
                        ],
                    ]
            )
            ?>
<?php } ?>
    </section>

</aside>
