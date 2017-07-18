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
                            ['label' => 'Список запросов', 'icon' => 'life-buoy', 'url' => ['agent-request/index'], 'options' => ['class' => 'hidden-xs']],
                            ['label' => 'Клиенты', 'icon' => 'users', 'url' => ['organization/agent'], 'options' => ['class' => 'hidden-xs']],
                        ],
                    ]
            )
            ?>
<?php } ?>
    </section>

</aside>
