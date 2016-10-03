<aside class="main-sidebar">

    <section class="sidebar">

        <?= dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu'],
                'items' => [
                    ['label' => 'Рабочий стол', 'icon' => 'fa fa-dashboard', 'url' => ['client/index']],
                    ['label' => 'Разместить заказ', 'icon' => 'fa fa-gears', 'url' => ['order/create']],
                    ['label' => 'История заказов', 'icon' => 'fa fa-gears', 'url' => ['order/index']],
                    ['label' => 'Мои поставщики', 'icon' => 'fa fa-gears', 'url' => ['client/suppliers']],
                    ['label' => 'Аналитика', 'icon' => 'fa fa-gears', 'url' => '#'],
                    ['label' => 'Обучающее видео', 'icon' => 'fa fa-gears', 'url' => '#'],
                    ['label' => 'Акции', 'icon' => 'fa fa-gears', 'url' => '#'],
                    ['label' => 'Настройки', 'icon' => 'fa fa-gears', 'url' => ['client/settings']],
                ],
            ]
        ) ?>

    </section>

</aside>
