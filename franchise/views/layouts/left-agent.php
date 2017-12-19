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
                            ['label' => Yii::t('app', 'Список запросов'), 'icon' => 'life-buoy', 'url' => ['agent-request/index'], 'options' => ['class' => 'hidden-xs']],
                            ['label' => Yii::t('app', 'Клиенты'), 'icon' => 'users', 'url' => ['organization/agent'], 'options' => ['class' => 'hidden-xs']],
                        ],
                    ]
            )
            ?>
<?php } ?>
    </section>

</aside>
