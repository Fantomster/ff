<?php

use yii\helpers\Url;
use common\models\Organization;

$user = Yii::$app->user->identity;

$roles = [
    \common\models\Role::ROLE_RESTAURANT_MANAGER,
    \common\models\Role::ROLE_FKEEPER_MANAGER,
    \common\models\Role::ROLE_ADMIN,
    \common\models\Role::getFranchiseeEditorRoles(),
];

$disabled_roles = [
    \common\models\Role::ROLE_RESTAURANT_ACCOUNTANT,
    \common\models\Role::ROLE_RESTAURANT_BUYER,
    \common\models\Role::ROLE_RESTAURANT_JUNIOR_BUYER,
    \common\models\Role::ROLE_RESTAURANT_ORDER_INITIATOR,
];

$franchiseeManager = $user->organization->getFranchiseeManagerInfo();
if ($franchiseeManager && $franchiseeManager->phone_manager) {
    if ($franchiseeManager->additional_number_manager) {
        $phoneUrl = $franchiseeManager->phone_manager . "p" . $franchiseeManager->additional_number_manager;
        $phone = $franchiseeManager->phone_manager . Yii::t('message', 'frontend.views.layouts.client.left.add', ['ru' => " доб. "]) . $franchiseeManager->additional_number_manager;
    } else {
        $phoneUrl = $franchiseeManager->phone_manager;
        $phone = $franchiseeManager->phone_manager;
    }
} else {
    $phoneUrl = "+7-499-404-10-18p202";
    $phone = Yii::t('message', 'frontend.views.layouts.client.left.phone', ['ru' => "+7-499-404-10-18 доб. 202"]);
}

$newOrdersCount = $user->organization->getNewOrdersCount();
$cartCount = $user->organization->getCartCount();

$vsdCount = $user->organization->getVsdCount();
$suppliersCount = $user->organization->getSuppliersCount();

$licenses = $user->organization->getLicenseList();
?>

<aside class="main-sidebar">

    <section class="sidebar">
        <?=
        dmstr\widgets\Menu::widget(
            [
                'options'      => ['class' => 'sidebar-menu tree', 'data-widget' => "tree"],
                'encodeLabels' => false,
                'items'        => [
                    ['label' => Yii::t('message', 'frontend.views.layouts.client.left.navigation', ['ru' => 'НАВИГАЦИЯ']), 'options' => ['class' => 'header']],
                    ['label' => Yii::t('message', 'frontend.views.layouts.client.left.desktop', ['ru' => 'Рабочий стол']), 'icon' => 'home', 'url' => ['/client/index'], 'visible' => ($user->role_id != \common\models\Role::ROLE_RESTAURANT_ORDER_INITIATOR)],
                    [
                        'label'    => Yii::t('message', 'frontend.views.layouts.client.left.set_order', ['ru' => 'Разместить заказ']),
                        'icon'     => 'opencart',
                        'url'      => ['/order/create'],
                        'template' => '<a href="{url}">{icon}{label}<span class="pull-right-container"><span class="label label-primary pull-right cartCount">' . $cartCount . '</span></span></a>',
                    ],
                    [
                        'label'    => Yii::t('message', 'frontend.views.layouts.client.left.orders', ['ru' => 'Заказы']),
                        'icon'     => 'history',
                        'url'      => ['/order/index'],
                        'template' => '<a href="{url}">{icon}{label}<span class="pull-right-container"><span class="label bg-yellow pull-right new-orders-count">' . ($newOrdersCount ? $newOrdersCount : '') . '</span></span></a>'
                    ],
                    ['label'   => Yii::t('message', 'frontend.views.layouts.client.left.vendors', ['ru' => 'Поставщики']), 'icon' => 'users', 'url' => ['/client/suppliers'], 'options' => ['class' => 'hidden-xs step-vendor'],
                     'visible' => (!in_array($user->role_id, $disabled_roles) && $user->role_id != \common\models\Role::ROLE_RESTAURANT_JUNIOR_BUYER) && $user->role_id != \common\models\Role::ROLE_RESTAURANT_ORDER_INITIATOR || $user->role_id == \common\models\Role::ROLE_RESTAURANT_BUYER],
//                        [
//                            'label' => 'Сообщения' . Html::tag('span', 4, ['class' => 'label label-danger pull-right']),
//                            'icon' => 'envelope',
//                            'url' => ['client/messages'],
//                            ],
                    ['label' => 'MARKET', 'icon' => 'shopping-cart', 'url' => Yii::$app->params['staticUrl'][Yii::$app->language]['market'], 'options' => ['class' => 'l-fmarket']],
                    ['label' => Yii::t('message', 'frontend.views.layouts.client.left.requests', ['ru' => 'Заявки']), 'icon' => 'paper-plane', 'url' => ['/request/list'], 'options' => ['class' => 'l-fmarket'], 'visible' => !in_array($user->role_id, $disabled_roles)],
                    ['label' => Yii::t('message', 'frontend.views.layouts.client.left.anal', ['ru' => 'Аналитика']), 'icon' => 'signal', 'url' => ['/client/analytics'], 'options' => ['class' => 'hidden-xs'], 'visible' => !in_array($user->role_id, $disabled_roles)],
                    ['label'   => Yii::t('message', 'frontend.views.layouts.client.left.fullmap', ['ru' => 'Сопоставление']), 'icon' => 'signal',
                     'url'     => ['/clientintegr/fullmap'],
                     'options' => ['class' => 'hidden-xs'],
                     'visible' => (!in_array($user->role_id, $disabled_roles) && !empty(Organization::getLicenseList()) && ($suppliersCount != 0))],
//                        ['label' => 'Обучающие видео', 'icon' => 'play-circle-o', 'url' => ['/client/tutorial', 'video' => 'video']],
                    // ['label' => 'Мои акции', 'icon' => 'fa fa-ticket', 'url' => ['client/events']],
                    //   ['label' => 'Новости', 'icon' => 'newspaper-o', 'url' => 'http://blog.mixcart.ru?news', 'options' => ['class' => 'hidden-xs']],
                    ['label'    => Yii::t('message', 'frontend.views.layouts.client.left.mercury', ['ru' => 'ВЕТИС "Меркурий"']),
                     'url'      => ['/clientintegr/merc/default'],
                     'options'  => ['class' => 'hidden-xs'],
                     'template' => '<a href="{url}"><img src="' . Yii::$app->request->baseUrl . '/img/mercuriy_icon.png" style="width: 18px; margin-right: 8px;">{label}<span class="pull-right-container"><span class="label label-primary pull-right">' . $vsdCount . '</span></span></a>',
                     'visible'  => (!in_array($user->role_id, $disabled_roles) || $user->role_id == \common\models\Role::ROLE_RESTAURANT_ACCOUNTANT),
                        /* 'items' => [
                          [
                          'label' => Yii::t('message', 'frontend.views.layouts.client.left.store_entry', ['ru'=>'Журнал продукции']),
                          'icon' => 'circle-o',
                          'url' => ['/clientintegr/merc/stock-entry'],
                          //'visible' => in_array($user->role_id,$roles)
                          ],
                          ], */
                    ],
                    [
                        'label'   => Yii::t('message', 'frontend.views.layouts.client.left.settings', ['ru' => 'Настройки']),
                        'icon'    => 'gears',
                        'url'     => '#', //['client/settings'],
                        'options' => ['class' => "hidden-xs"],
                        'visible' => (!in_array($user->role_id, $disabled_roles) || $user->role_id == \common\models\Role::ROLE_RESTAURANT_ACCOUNTANT),
                        'items'   => [
                            [
                                'label'   => Yii::t('message', 'frontend.views.layouts.client.left.custom', ['ru' => 'Общие']),
                                'icon'    => 'circle-o',
                                'url'     => ['/client/settings'],
                                'visible' => !($user->role_id == \common\models\Role::ROLE_RESTAURANT_ACCOUNTANT)//in_array($user->role_id,$roles)
                            ],
                            [
                                'label'   => Yii::t('message', 'frontend.views.layouts.client.left.integrations', ['ru' => 'Интеграции']),
                                'icon'    => 'circle-o',
                                'url'     => ['/clientintegr/default'],
                                'visible' => (!empty($licenses))
                            ],
                            [
                                'label'   => Yii::t('message', 'frontend.views.layouts.client.left.employees', ['ru' => 'Сотрудники']),
                                'icon'    => 'circle-o',
                                'url'     => ['/client/employees'],
                                'visible' => !($user->role_id == \common\models\Role::ROLE_RESTAURANT_ACCOUNTANT)//in_array($user->role_id,$roles)
                            ],
                            [
                                'label'   => Yii::t('message', 'frontend.views.layouts.client.left.notifications', ['ru' => 'Уведомления']),
                                'icon'    => 'circle-o',
                                'url'     => ['/settings/notifications'],
                                'visible' => !($user->role_id == \common\models\Role::ROLE_RESTAURANT_ACCOUNTANT) //in_array($user->role_id,$roles)
                            ],
                            [
                                'label'   => Yii::t('app', 'Платежи'),
                                'icon'    => 'circle-o',
                                'url'     => ['/client/payments'],
                                'visible' => !($user->role_id == \common\models\Role::ROLE_RESTAURANT_ACCOUNTANT)//in_array($user->role_id,$roles)
                            ],
                        ]
                    ],
                    // ['label' => 'Поддержка', 'icon' => 'support', 'url' => ['client/support']],
                    ['label' => Yii::t('message', 'frontend.views.layouts.client.left.send_notification', ['ru' => 'ОТПРАВИТЬ ПРИГЛАШЕНИЕ']), 'options' => ['class' => 'header'], 'visible' => ($user->role_id != \common\models\Role::ROLE_RESTAURANT_ORDER_INITIATOR)],
                ],
            ]
        )
        ?>
        <?php if ($user->role_id != \common\models\Role::ROLE_RESTAURANT_ORDER_INITIATOR) : ?>
            <form action="<?= Url::to(['/user/ajax-invite-friend']) ?>" method="post" style="margin: 15px;"
                  id="inviteForm">
                <div class="input-group input-group-sm" data-toggle="tooltip" data-placement="bottom" title=""
                     style="color: rgb(255, 255, 255);font-size: 20px;"
                     data-original-title="<?= Yii::t('message', 'frontend.views.layouts.client.left.invite', ['ru' => 'Пригласите партнеров и друзей']) ?>">
                    <input type="text" class="form-control" placeholder="Email" name="email" id="email">
                    <span class="input-group-btn">
                    <button type="submit" class="btn btn-success btn-flat" id="inviteFriend">
                        <i class="fa fa-paper-plane m-r-xxs" style="margin-top:-3px;"></i>
                    </button>
                </span>
                </div>
            </form>
        <?php endif; ?>

        <ul class="sidebar-menu personal-manager">
            <li class="header"><span
                        style="text-transform: uppercase;"><?= Yii::t('message', 'frontend.views.layouts.client.left.techno', ['ru' => 'ТЕХНИЧЕСКАЯ ПОДДЕРЖКА']) ?></span>
            </li>
            <div style="text-align: center; color: #d8d7d7;padding-top:10px">
                <p>
                    <a href="tel:<?php echo $phoneUrl; ?>">
                        <i class="fa fa-phone"></i> <?php echo $phone; ?>
                    </a>
                </p>
            </div>
        </ul>
    </section>
</aside>
<?php
/* $sidebar_js = <<< JS
  $(window).resize(function(){
  $('#inviteForm').css('position','absolute').css('top',$(window).height()-60).removeClass('hide');
  }); $('#inviteForm').css('position','absolute').css('top',$(window).height()-60).removeClass('hide');
  JS;
  $this->registerJs($sidebar_js, View::POS_READY); */
?>
