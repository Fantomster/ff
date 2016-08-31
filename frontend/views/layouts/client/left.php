<aside class="main-sidebar">

    <section class="sidebar">

        <?= dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu'],
                'items' => [
                    ['label' => 'Главная', 'icon' => 'fa fa-dashboard', 'url' => ['client/index']],
                    ['label' => 'Настройки', 'icon' => 'fa fa-gears', 'url' => ['client/settings']],
                ],
            ]
        ) ?>

    </section>

</aside>
