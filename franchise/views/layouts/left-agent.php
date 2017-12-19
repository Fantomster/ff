<aside class="main-sidebar">

    <section class="sidebar">
        <?php if (!Yii::$app->user->isGuest) { ?>
            <?=
            dmstr\widgets\Menu::widget(
                    [
                        'options' => ['class' => 'sidebar-menu'],
                        'encodeLabels' => false,
                        'items' => [
                            ['label' => Yii::t('app', 'franchise.views.layouts.navi_two', ['ru'=>'НАВИГАЦИЯ']), 'options' => ['class' => 'header']],
                            ['label' => Yii::t('app', 'franchise.views.layouts.req_list', ['ru'=>'Список запросов']), 'icon' => 'life-buoy', 'url' => ['agent-request/index'], 'options' => ['class' => 'hidden-xs']],
                            ['label' => Yii::t('app', 'franchise.views.layouts.clients', ['ru'=>'Клиенты']), 'icon' => 'users', 'url' => ['organization/agent'], 'options' => ['class' => 'hidden-xs']],
                        ],
                    ]
            )
            ?>
<?php } ?>
    </section>

</aside>
