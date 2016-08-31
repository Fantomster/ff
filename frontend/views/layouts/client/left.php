<aside class="main-sidebar">

    <section class="sidebar">

        <?= dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu'],
                'items' => [
                    //['label' => 'Menu Yii2', 'options' => ['class' => 'header']],
                    ['label' => 'Главная', 'icon' => 'fa fa-gears', 'url' => ['client/index']],
                    ['label' => 'Настройки', 'icon' => 'fa fa-gears', 'url' => ['client/settings']],
                ],
            ]
        ) ?>

    </section>

</aside>
