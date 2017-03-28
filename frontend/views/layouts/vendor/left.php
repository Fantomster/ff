<?php

use yii\helpers\Html;
use yii\web\View;
use yii\helpers\Url;

$user = Yii::$app->user->identity;
$newOrdersCount = $user->organization->getNewOrdersCount();
$newClientCount = $user->organization->getNewClientCount();
$cartCount = $user->organization->getCartCount();
?>

<aside class="main-sidebar">

    <section class="sidebar">
        <?=
        dmstr\widgets\Menu::widget(
                [
                    'options' => ['class' => 'sidebar-menu'],
                    'encodeLabels' => false,
                    'items' => [
                        ['label' => 'НАВИГАЦИЯ', 'options' => ['class' => 'header']],
                        ['label' => 'Рабочий стол', 'icon' => 'fa fa-home', 'url' => ['vendor/index']],
                        [
                            'label' => 'Заказы',
                            'icon' => 'fa fa-history',
                            'url' => ['order/index'],
                            'template' => '<a href="{url}">{icon}{label}<span class="pull-right-container"><span class="label bg-yellow pull-right new-orders-count">'.($newOrdersCount ? $newOrdersCount : '').'</span></span></a>',
                        ],
                        ['label' => 'Мои каталоги', 'icon' => 'fa fa-list-alt', 'url' => ['vendor/catalogs'], 'options' => ['class' => 'hidden-xs']],
//                        ['label' => 'Сообщения' . Html::tag('span', 4, ['class' => 'label label-danger pull-right']), 'icon' => 'fa fa-envelope', 'url' => ['vendor/messages']],
                        ['label' => 'F-MARKET', 'icon' => 'fa fa-shopping-cart', 'url' => 'http://market.f-keeper.ru', 'options' => ['class' => 'l-fmarket']],
                        ['label' => 'Заявки<sub class="sub-new">NEW</sub>', 'icon' => 'fa fa-paper-plane', 'url' => ['request/list'], 'options' => ['class' => 'l-fmarket']],
                        [
                            'label' => 'Мои клиенты', 
                            'icon' => 'fa fa-users', 
                            'url' => ['vendor/clients'],
                            'template' => '<a href="{url}">{icon}{label}<span class="pull-right-container"><span class="label bg-yellow pull-right">'.($newClientCount ? $newClientCount : '').'</span></span></a>',
                        ],
                        ['label' => 'Аналитика', 'icon' => 'fa fa-signal', 'url' => ['vendor/analytics'], 'options' => ['class' => 'hidden-xs']],
                        ['label' => 'Обучающие видео', 'icon' => 'fa fa-play-circle-o', 'url' => ['vendor/tutorial', 'video' => 'video']],
                        //['label' => 'Мои акции', 'icon' => 'fa fa-ticket', 'url' => ['vendor/events']],
                        ['label' => 'Новости', 'icon' => 'fa fa-newspaper-o', 'url' => 'http://blog.f-keeper.ru?news', 'options' => ['class' => 'hidden-xs']],
                        [
                            'label' => 'Настройки',
                            'icon' => 'fa fa-gears',
                            'url' => '#',
                            'options' => ['class' => "treeview hidden-xs"],
                            'items' => [
                                ['label' => 'Общие', 'icon' => 'fa fa-circle-o', 'url' => ['vendor/settings']],
                                ['label' => 'Сотрудники', 'icon' => 'fa fa-circle-o', 'url' => ['vendor/employees']],
                                ['label' => 'Доставка', 'icon' => 'fa fa-circle-o', 'url' => ['vendor/delivery']],
                            ]
                        ],
                        
                        //['label' => 'Поддержка', 'icon' => 'fa fa-support', 'url' => ['vendor/support']],
                        ['label' => 'ОТПРАВИТЬ ПРИГЛАШЕНИЕ', 'options' => ['class' => 'header']],
                    ],
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
            <li class="header"><span style="text-transform: uppercase;">Личный менеджер</span></li>
            <div style="text-align: center; color: #d8d7d7;">
                <img src="images/welcome-zalina.png" class="welcome-manager">
                <p style="font-size: 14px;"><strong>Залина</strong></p><p></p>
                <p>+7 929 611 79 00</p>
            </div>
        </ul>
    </section>
</aside>