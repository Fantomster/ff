<aside class="main-sidebar">

    <section class="sidebar">

        <?= dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu'],
                'items' => [
                    ['label' => 'Рабочий стол', 'icon' => 'fa fa-dashboard', 'url' => ['vendor/index']],
                    ['label' => 'История заказов', 'icon' => 'fa fa-gears', 'url' => ['order/index']],
                    ['label' => 'Мои каталоги', 'icon' => 'fa fa-list-alt', 'url' => ['vendor/catalogs']],
                    ['label' => 'Сообщения', 'icon' => 'fa fa-gears', 'url' => ['vendor/settings']],
                    ['label' => 'Мои клиенты', 'icon' => 'fa fa-users', 'url' => ['vendor/clients']],
                    ['label' => 'Мои акции', 'icon' => 'fa fa-gears', 'url' => ['vendor/settings']],
                    ['label' => 'Аналитика', 'icon' => 'fa fa-gears', 'url' => ['vendor/settings']],
                    ['label' => 'Обучающее видео', 'icon' => 'fa fa-gears', 'url' => ['vendor/settings']],
                    ['label' => 'Создать распродажу', 'icon' => 'fa fa-gears', 'url' => ['vendor/settings']],
                    ['label' => 'Настройки', 'icon' => 'fa fa-gears', 'url' => ['vendor/settings']],
                ],
            ]
        ) ?>

    </section>

</aside>
