<?php

use yii\helpers\Html;
use yii\web\View;
use yii\helpers\Url;

$user = Yii::$app->user->identity;

$roles = [
    \common\models\Role::ROLE_SUPPLIER_MANAGER,
    \common\models\Role::ROLE_FKEEPER_MANAGER,
    \common\models\Role::ROLE_ADMIN,
    \common\models\Role::getFranchiseeEditorRoles(),
];

$franchiseeManager = $user->organization->getFranchiseeManagerInfo();
if ($franchiseeManager && $franchiseeManager->phone_manager) {
    if ($franchiseeManager->additional_number_manager) {
        $phoneUrl = $franchiseeManager->phone_manager . "p" . $franchiseeManager->additional_number_manager;
        $phone = $franchiseeManager->phone_manager . Yii::t('message', 'frontend.views.layouts.left_additional', ['ru' => " доб. "]) . $franchiseeManager->additional_number_manager;
    } else {
        $phoneUrl = $franchiseeManager->phone_manager;
        $phone = $franchiseeManager->phone_manager;
    }
} else {
    $phoneUrl = "+7-499-404-10-18p202";
    $phone = Yii::t('message', 'frontend.views.layouts.left_phone', ['ru' => "+7-499-404-10-18 доб. 202"]);
}
$manager_id = Yii::$app->user->can('manage') ? null : $user->id;
$newOrdersCount = $user->organization->getNewOrdersCount($manager_id);
$newClientCount = Yii::$app->user->can('manage') ? $user->organization->getNewClientCount() : 0;

$vsdCount = $user->organization->getVsdCount();

$licenses = $user->organization->getLicenseList();

$menuItems = [
    ['label' => Yii::t('message', 'frontend.views.layouts.left.navi', ['ru' => 'НАВИГАЦИЯ']), 'options' => ['class' => 'header']],
    ['label' => Yii::t('message', 'frontend.views.layouts.left.desktop', ['ru' => 'Рабочий стол']), 'icon' => 'home', 'url' => ['/vendor/index']],
    [
        'label' => Yii::t('message', 'frontend.views.layouts.left.orders', ['ru' => 'Заказы']),
        'icon' => 'history',
        'url' => ['/order/index'],
        'template' => '<a href="{url}">{icon}{label}<span class="pull-right-container"><span class="label bg-yellow pull-right new-orders-count">' . ($newOrdersCount ? $newOrdersCount : '') . '</span></span></a>',
    ],
    ['label' => Yii::t('message', 'frontend.views.layouts.left.catalogs', ['ru' => 'Мои каталоги']), 'icon' => 'list-alt', 'url' => ['/vendor/catalogs'], 'options' => ['class' => 'hidden-xs']],
//                        ['label' => 'Сообщения' . Html::tag('span', 4, ['class' => 'label label-danger pull-right']), 'icon' => 'fa fa-envelope', 'url' => ['vendor/messages']],
    ['label' => 'MARKET', 'icon' => 'shopping-cart', 'url' => Yii::$app->params['staticUrl'][Yii::$app->language]['market'], 'options' => ['class' => 'l-fmarket']],
    ['label' => Yii::t('message', 'frontend.views.layouts.left.requests', ['ru' => 'Заявки']), 'icon' => 'paper-plane', 'url' => ['/request/list'], 'options' => ['class' => 'l-fmarket']],
    [
        'label' => Yii::t('message', 'frontend.views.layouts.left.my_clients', ['ru' => 'Мои клиенты']),
        'icon' => 'users',
        'url' => ['/vendor/clients'],
        'template' => '<a href="{url}">{icon}{label}<span class="pull-right-container"><span class="label bg-yellow pull-right">' . ($newClientCount ? $newClientCount : '') . '</span></span></a>',
    ],
    ['label' => Yii::t('message', 'frontend.views.layouts.left.anal', ['ru' => 'Аналитика']), 'icon' => 'signal', 'url' => ['/vendor/analytics'], 'options' => ['class' => 'hidden-xs']],
        //['label' => 'Обучающие видео', 'icon' => 'play-circle-o', 'url' => ['/vendor/tutorial', 'video' => 'video']],
        //['label' => 'Мои акции', 'icon' => 'ticket', 'url' => ['vendor/events']],
        // ['label' => 'Новости', 'icon' => 'newspaper-o', 'url' => 'http://blog.mixcart.ru?news', 'options' => ['class' => 'hidden-xs']],
        //['label' => 'Поддержка', 'icon' => 'support', 'url' => ['vendor/support']],
    ['label' => Yii::t('message', 'frontend.views.layouts.client.left.mercury', ['ru'=>'ВЕТИС "Меркурий"']),
        'url' => ['/clientintegr/merc/default'],
        'options' => ['class' => 'hidden-xs'],
        'template' => '<a href="{url}"><img src="'.Yii::$app->request->baseUrl.'/img/mercuriy_icon.png" style="width: 18px; margin-right: 8px;">{label}<span class="pull-right-container"><span class="label label-primary pull-right">' . $vsdCount . '</span></span></a>',
        //'visible' => isset($licenses['mercury'])
        /*'items' => [
            [
                'label' => Yii::t('message', 'frontend.views.layouts.client.left.store_entry', ['ru'=>'Журнал продукции']),
                'icon' => 'circle-o',
                'url' => ['/clientintegr/merc/stock-entry'],
                //'visible' => in_array($user->role_id,$roles)
            ],
        ],*/
    ],
];
if (in_array($user->role_id, $roles) || Yii::$app->user->can('manage')) {

    $menuItems[] = [
        'label' => Yii::t('message', 'frontend.views.layouts.left.settings', ['ru' => 'Настройки']),
        'icon' => 'gears',
        'url' => '#',
        'options' => ['class' => "treeview hidden-xs"],
        'items' => [
            ['label' => Yii::t('message', 'frontend.views.layouts.left.custom', ['ru' => 'Общие']), 'icon' => 'circle-o', 'url' => ['/vendor/settings']],
            [
                'label' => Yii::t('message', 'frontend.views.layouts.client.left.integrations', ['ru'=>'Интеграции']),
                'icon' => 'circle-o',
                'url' => ['/clientintegr/default'],
                'visible' => (!empty($licenses))
            ],
            ['label' => Yii::t('message', 'frontend.views.layouts.left.employees', ['ru' => 'Сотрудники']), 'icon' => 'circle-o', 'url' => ['/vendor/employees']],
            ['label' => Yii::t('message', 'frontend.views.layouts.left.notifications', ['ru' => 'Уведомления']), 'icon' => 'circle-o', 'url' => ['/settings/notifications']],
            ['label' => Yii::t('message', 'frontend.views.layouts.left.delivery', ['ru' => 'Доставка']), 'icon' => 'circle-o', 'url' => ['/vendor/delivery']],
            [
                'label' => Yii::t('app', 'Платежи'),
                'icon' => 'circle-o',
                'url' => ['/vendor/payments'],
                'visible' => in_array($user->role_id, $roles)
            ],
        ]
    ];
} else {
    $menuItems[] = [
        'label' => Yii::t('message', 'frontend.views.layouts.left.settings', ['ru' => 'Настройки']),
        'icon' => 'gears',
        'url' => '#',
        'options' => ['class' => "treeview hidden-xs"],
        'items' => [
            ['label' => Yii::t('message', 'frontend.views.layouts.left.notifications_two', ['ru' => 'Уведомления']), 'icon' => 'circle-o', 'url' => ['/settings/notifications']],
        ]
    ];
}
$menuItems[] = ['label' => Yii::t('message', 'frontend.views.layouts.left.send_invitations', ['ru' => 'ОТПРАВИТЬ ПРИГЛАШЕНИЕ']), 'options' => ['class' => 'header']];
?>

<aside class="main-sidebar">

    <section class="sidebar">
        <?=
        dmstr\widgets\Menu::widget(
                [
                    'options' => ['class' => 'sidebar-menu tree', 'data-widget' => "tree"],
                    'encodeLabels' => false,
                    'items' => $menuItems,
                ]
        )
        ?> 
        <form action="<?= Url::to(['/user/ajax-invite-friend']) ?>" method="post" style="margin: 15px;" id="inviteForm">
            <div class="input-group input-group-sm" data-toggle="tooltip" data-placement="bottom" title="" style="color: rgb(255, 255, 255); font-size: 20px;" data-original-title="<?= Yii::t('message', 'frontend.views.layouts.left.invite_partners', ['ru' => 'Пригласите партнеров и друзей']) ?>">
                <input type="text" class="form-control" placeholder="Email" name="email" id="email">
                <span class="input-group-btn">
                    <button type="submit" class="btn btn-success btn-flat" id="inviteFriend">
                        <i class="fa fa-paper-plane m-r-xxs" style="margin-top:-3px;"></i>
                    </button>
                </span>
            </div>
        </form>
        <ul class="sidebar-menu personal-manager">
            <li class="header"><span style="text-transform: uppercase;"><?= Yii::t('message', 'frontend.views.layouts.left.techno', ['ru' => 'ТЕХНИЧЕСКАЯ ПОДДЕРЖКА']) ?></span></li>
            <br>
            <div style="text-align: center; color: #d8d7d7;">
                <p><a href="tel:<?= $phoneUrl ?>"><i class="fa fa-phone"></i> <?= $phone ?></a></p>
            </div>
        </ul>
    </section>
</aside>
