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
                        ['label' => 'Рестораны', 'icon' => 'cutlery', 'url' => ['organization/clients'], 'options' => ['class' => 'hidden-xs']],
                        ['label' => 'Поставщики', 'icon' => 'users', 'url' => ['organization/vendors'], 'options' => ['class' => 'hidden-xs']],
                        ['label' => 'Заказы', 'icon' => 'history', 'url' => ['site/orders'], 'options' => ['class' => 'hidden-xs']],
                    ],
                ]
            )
            ?>
        <?php } ?>
    </section>

</aside>