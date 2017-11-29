<aside class="main-sidebar">

    <section class="sidebar">
        <?php if (!Yii::$app->user->isGuest) { ?>
            <?=
            dmstr\widgets\Menu::widget(
                [
                    'options' => ['class' => 'sidebar-menu'],
                    'encodeLabels' => false,
                    'items' => [
                        ['label' => Yii::t('app', 'franchise.views.layouts.fin_two', ['ru'=>'Финансы']), 'icon' => 'money', 'url' => ['finance/index'], 'options' => ['class' => 'hidden-xs']],
                    ],
                ]
            )
            ?>
        <?php } ?>
    </section>

</aside>