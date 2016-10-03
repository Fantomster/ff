<aside class="main-sidebar">

    <section class="sidebar">

        <?= dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu'],
                'items' => [
                    ['label' => 'Рабочий стол', 'icon' => 'fa fa-home', 'url' => ['client/index']],
                    ['label' => 'Разместить заказ', 'icon' => 'fa fa-opencart', 'url' => ['order/create']],
                    ['label' => 'История заказов', 'icon' => 'fa fa-history', 'url' => ['order/index']],
                    ['label' => 'Мои поставщики', 'icon' => 'fa fa-users', 'url' => ['client/suppliers']],
                    ['label' => 'Сообщения', 'icon' => 'fa fa-envelop', 'url' => ['client/settings']],
                    ['label' => 'Аналитика', 'icon' => 'fa fa-signal', 'url' => ['client/settings']],
                    ['label' => 'Обучающее видео', 'icon' => 'fa fa-play-circle-o', 'url' => ['client/settings']],
                    ['label' => 'Акции', 'icon' => 'fa fa-ticket', 'url' => ['client/settings']],
                    ['label' => 'Настройки', 'icon' => 'fa fa-gears', 'url' => ['client/settings']],
                ],
            ]
        ) ?>

    </section>

</aside>
