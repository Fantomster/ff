<?php

use yii\helpers\Html;
use yii\web\View;
use yii\helpers\Url;

$user = Yii::$app->user->identity;

$franchiseeManager = $user->organization->getFranchiseeManagerInfo();
if ($franchiseeManager && $franchiseeManager->phone_manager) {
    if ($franchiseeManager->additional_number_manager) {
        $phoneUrl = $franchiseeManager->phone_manager . "p" . $franchiseeManager->additional_number_manager;
        $phone = $franchiseeManager->phone_manager . " доб. " . $franchiseeManager->additional_number_manager;
    } else {
        $phoneUrl = $franchiseeManager->phone_manager;
        $phone = $franchiseeManager->phone_manager;
    }
} else {
    $phoneUrl = "+7-499-404-10-18p202";
    $phone = "+7-499-404-10-18 доб. 202";
}
$manager_id = Yii::$app->user->can('manage') ? null : $user->id;
$newOrdersCount = $user->organization->getNewOrdersCount($manager_id);
$newClientCount = Yii::$app->user->can('manage') ? $user->organization->getNewClientCount() : 0;

$menuItems = [
    ['label' => 'НАВИГАЦИЯ', 'options' => ['class' => 'header']],
    ['label' => 'Рабочий стол', 'icon' => 'home', 'url' => ['/vendor/index']],
    [
        'label' => 'Заказы',
        'icon' => 'history',
        'url' => ['/order/index'],
        'template' => '<a href="{url}">{icon}{label}<span class="pull-right-container"><span class="label bg-yellow pull-right new-orders-count">' . ($newOrdersCount ? $newOrdersCount : '') . '</span></span></a>',
    ],
    ['label' => 'Мои каталоги', 'icon' => 'list-alt', 'url' => ['/vendor/catalogs'], 'options' => ['class' => 'hidden-xs']],
//                        ['label' => 'Сообщения' . Html::tag('span', 4, ['class' => 'label label-danger pull-right']), 'icon' => 'fa fa-envelope', 'url' => ['vendor/messages']],
    ['label' => 'F-MARKET', 'icon' => 'shopping-cart', 'url' => 'http://market.f-keeper.ru', 'options' => ['class' => 'l-fmarket']],
    ['label' => 'Заявки', 'icon' => 'paper-plane', 'url' => ['/request/list'], 'options' => ['class' => 'l-fmarket']],
    [
        'label' => 'Мои клиенты',
        'icon' => 'users',
        'url' => ['/vendor/clients'],
        'template' => '<a href="{url}">{icon}{label}<span class="pull-right-container"><span class="label bg-yellow pull-right">' . ($newClientCount ? $newClientCount : '') . '</span></span></a>',
    ],
    ['label' => 'Аналитика', 'icon' => 'signal', 'url' => ['/vendor/analytics'], 'options' => ['class' => 'hidden-xs']],
    ['label' => 'Обучающие видео', 'icon' => 'play-circle-o', 'url' => ['/vendor/tutorial', 'video' => 'video']],
    //['label' => 'Мои акции', 'icon' => 'ticket', 'url' => ['vendor/events']],
    ['label' => 'Новости', 'icon' => 'newspaper-o', 'url' => 'http://blog.f-keeper.ru?news', 'options' => ['class' => 'hidden-xs']],
        //['label' => 'Поддержка', 'icon' => 'support', 'url' => ['vendor/support']],
];
if (Yii::$app->user->can('manage')) {
    $menuItems[] = [
        'label' => 'Настройки',
        'icon' => 'gears',
        'url' => '#',
        'options' => ['class' => "treeview hidden-xs"],
        'items' => [
            ['label' => 'Общие', 'icon' => 'circle-o', 'url' => ['/vendor/settings']],
            //   ['label' => 'Интеграции', 'icon' => 'circle-o', 'url' => ['/vendorintegr/default']],
            ['label' => 'Сотрудники', 'icon' => 'circle-o', 'url' => ['/vendor/employees']],
//            ['label' => 'Уведомления', 'icon' => 'circle-o', 'url' => ['/settings/notifications']],
            ['label' => 'Доставка', 'icon' => 'circle-o', 'url' => ['/vendor/delivery']],
        ]
    ];
}
$menuItems[] = ['label' => 'ОТПРАВИТЬ ПРИГЛАШЕНИЕ', 'options' => ['class' => 'header']];
?>

<aside class="main-sidebar">

    <section class="sidebar">
        <?=
        dmstr\widgets\Menu::widget(
                [
                    'options' => ['class' => 'sidebar-menu'],
                    'encodeLabels' => false,
                    'items' => $menuItems,
                ]
        )
        ?> 
        <form action="<?= Url::to(['/user/ajax-invite-friend']) ?>" method="post" style="margin: 15px;" id="inviteForm">
            <div class="input-group input-group-sm" data-toggle="tooltip" data-placement="bottom" title="" style="color: rgb(255, 255, 255); font-size: 20px;" data-original-title="Пригласите партнеров и друзей">
                <input type="text" class="form-control" placeholder="Email" name="email" id="email">
                <span class="input-group-btn">
                    <button type="submit" class="btn btn-success btn-flat" id="inviteFriend">
                        <i class="fa fa-paper-plane m-r-xxs" style="margin-top:-3px;"></i>
                    </button>
                </span>
            </div>
        </form>
        <ul class="sidebar-menu personal-manager">
            <li class="header"><span style="text-transform: uppercase;">ТЕХНИЧЕСКАЯ ПОДДЕРЖКА</span></li>
            <div style="text-align: center; color: #d8d7d7;">
                <p><a href="tel:<?= $phoneUrl ?>"><i class="fa fa-phone"></i> <?= $phone ?></a></p>
            </div>
        </ul>
    </section>
</aside>
