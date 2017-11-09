<aside class="main-sidebar">

    <section class="sidebar">
        <?php if (!Yii::$app->user->isGuest) { ?>
            <?=
            dmstr\widgets\Menu::widget(
                [
                    'options' => ['class' => 'sidebar-menu'],
                    'encodeLabels' => false,
                    'items' => [
                        ['label' => Yii::t('app', 'НАВИГАЦИЯ'), 'options' => ['class' => 'header']],
                        ['label' => Yii::t('app', 'Рестораны'), 'icon' => 'cutlery', 'url' => ['organization/clients'], 'options' => ['class' => 'hidden-xs']],
                        ['label' => Yii::t('app', 'Поставщики'), 'icon' => 'users', 'url' => ['organization/vendors'], 'options' => ['class' => 'hidden-xs']],
                        ['label' => Yii::t('app', 'Заказы'), 'icon' => 'history', 'url' => ['site/orders'], 'options' => ['class' => 'hidden-xs']],
                    ],
                ]
            )
            ?>
        <?php } ?>
    </section>

</aside>