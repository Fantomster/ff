<aside class="main-sidebar">

    <section class="sidebar">

        <?= dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu'],
                'items' => [
                    ['label' => 'Рабочий стол', 'icon' => 'fa fa-home', 'url' => ['vendor/index']],
                    ['label' => 'История заказов', 'icon' => 'fa fa-history', 'url' => ['order/index']],
                    ['label' => 'Мои каталоги', 'icon' => 'fa fa-list-alt', 'url' => ['vendor/catalogs']],
                    ['label' => 'Сообщения', 'icon' => 'fa fa-envelope', 'url' => ['vendor/settings']],
                    ['label' => 'Мои клиенты', 'icon' => 'fa fa-users', 'url' => ['vendor/clients']],
                    ['label' => 'Мои акции', 'icon' => 'fa fa-ticket', 'url' => ['vendor/settings']],
                    ['label' => 'Аналитика', 'icon' => 'fa fa-signal', 'url' => ['vendor/settings']],
                    ['label' => 'Обучающее видео', 'icon' => 'fa fa-play-circle-o', 'url' => ['vendor/settings']],
                    ['label' => 'Настройки', 'icon' => 'fa fa-gears', 'url' => ['vendor/settings']],
                ],
            ]
        ) ?>

    </section>

</aside>
