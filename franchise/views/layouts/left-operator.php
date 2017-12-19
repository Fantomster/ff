<aside class="main-sidebar">

    <section class="sidebar">
        <?php if (!Yii::$app->user->isGuest) { ?>
            <?=
            dmstr\widgets\Menu::widget(
                [
                    'options' => ['class' => 'sidebar-menu'],
                    'encodeLabels' => false,
                    'items' => [
                        ['label' => Yii::t('app', 'franchise.views.layouts.navi_five', ['ru'=>'НАВИГАЦИЯ']), 'options' => ['class' => 'header']],
                        ['label' => Yii::t('app', 'franchise.views.layouts.rest_four', ['ru'=>'Рестораны']), 'icon' => 'cutlery', 'url' => ['organization/clients'], 'options' => ['class' => 'hidden-xs']],
                        ['label' => Yii::t('app', 'franchise.views.layouts.vendors_four', ['ru'=>'Поставщики']), 'icon' => 'users', 'url' => ['organization/vendors'], 'options' => ['class' => 'hidden-xs']],
                        ['label' => Yii::t('app', 'franchise.views.layouts.orders_five', ['ru'=>'Заказы']), 'icon' => 'history', 'url' => ['site/orders'], 'options' => ['class' => 'hidden-xs']],
                    ],
                ]
            )
            ?>
        <?php } ?>
    </section>

</aside>