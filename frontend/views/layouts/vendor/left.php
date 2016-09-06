<aside class="main-sidebar">

    <section class="sidebar">

        <?= dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu'],
                'items' => [
                    ['label' => 'Главная', 'icon' => 'fa fa-dashboard', 'url' => ['vendor/index']],
                   // ['label' => 'Настройки', 'icon' => 'fa fa-gears', 'url' => ['vendor/settings']],
                    ['label' => 'Мои каталоги', 'icon' => 'fa fa-gears', 'url' => ['vendor/catalogs']],
                ],
            ]
        ) ?>

    </section>

</aside>
