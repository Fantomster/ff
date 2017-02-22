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
                            ['label' => 'Рабочий стол', 'icon' => 'fa fa-home', 'url' => ['app/index']],
                            ['label' => 'Рестораны', 'icon' => 'fa fa-cutlery', 'url' => ['organization/clients'], 'options' => ['class' => 'hidden-xs']],
                            ['label' => 'Поставщики', 'icon' => 'fa fa-users', 'url' => ['organization/vendors'], 'options' => ['class' => 'hidden-xs']],
                            ['label' => 'Аналитика', 'icon' => 'fa fa-signal', 'url' => ['analytics/index'], 'options' => ['class' => 'hidden-xs']],
                            ['label' => 'Финансы', 'icon' => 'fa fa-money', 'url' => ['finance/index'], 'options' => ['class' => 'hidden-xs']],
                            ['label' => 'Промо', 'icon' => 'fa fa-gift', 'url' => ['app/promotion'], 'options' => ['class' => 'hidden-xs']],
                            [
                                'label' => 'Настройки',
                                'icon' => 'fa fa-gears',
                                'url' => '#', //['client/settings'],
                                'options' => ['class' => "treeview hidden-xs"],
                                'items' => [
                                    ['label' => 'Общие', 'icon' => 'fa fa-circle-o', 'url' => ['app/settings']],
                                    ['label' => 'Сотрудники', 'icon' => 'fa fa-circle-o', 'url' => ['app/users']],
                                ]
                            ],
                        ],
                    ]
            )
            ?>
<?php } ?>
    </section>

</aside>
