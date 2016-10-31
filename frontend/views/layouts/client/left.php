<?php

use yii\helpers\Html;
use yii\helpers\Url;

$user = Yii::$app->user->identity;
$newOrdersCount = $user->organization->getNewOrdersCount();
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
                        ['label' => 'Рабочий стол', 'icon' => 'fa fa-home', 'url' => ['client/index']],
                        ['label' => 'Разместить заказ' . Html::tag('span', $cartCount, ['class' => 'label label-primary pull-right cartCount']), 'icon' => 'fa fa-opencart', 'url' => ['order/create']],
                        ['label' => 'Заказы' . ($newOrdersCount ? Html::tag('span', $newOrdersCount, ['class' => 'label bg-yellow pull-right']) : ''), 'icon' => 'fa fa-history', 'url' => ['order/index']],
                        ['label' => 'Поставщики', 'icon' => 'fa fa-users', 'url' => ['client/suppliers'], 'options' => ['class' => 'hidden-xs']],
                        
                        ['label' => 'Сообщения' . Html::tag('span', 4, ['class' => 'label label-danger pull-right']), 'icon' => 'fa fa-envelope', 'url' => ['client/messages']],
                        ['label' => 'Аналитика', 'icon' => 'fa fa-signal', 'url' => ['client/analytics'],'options' => ['class' => 'hidden-xs']],
                       // ['label' => 'Обучающее видео', 'icon' => 'fa fa-play-circle-o', 'url' => ['client/tutorial']],
                       // ['label' => 'Мои акции', 'icon' => 'fa fa-ticket', 'url' => ['client/events']],
                        [
                            'label' => 'Настройки',
                            'icon' => 'fa fa-gears',
                            'url' => '#',//['client/settings'],
                            'options' => ['class' => "treeview hidden-xs"],
                            'items' => [
                                ['label' => 'Общие', 'icon' => 'fa fa-circle-o', 'url' => ['client/settings']],
                                ['label' => 'Сотрудники', 'icon' => 'fa fa-circle-o', 'url' => ['client/employees']],
                            ]
                            ],
                       // ['label' => 'Поддержка', 'icon' => 'fa fa-support', 'url' => ['client/support']],
                        ['label' => 'ОТПРАВИТЬ ПРИГЛАШЕНИЕ', 'options' => ['class' => 'header']],
                    ],
                ]
        )
        ?>
        <form action="<?= Url::to(['/user/ajax-invite-friend']) ?>" method="post" style="margin: 15px;" id="inviteForm">
            <!--<a class="pull-right" href="#" data-toggle="tooltip" data-placement="left" title="" style="color: rgb(255, 255, 255); font-size: 20px;" data-original-title="Never show me this again!">-->
            <div class="input-group input-group-sm" data-toggle="tooltip" data-placement="bottom" title="" style="color: rgb(255, 255, 255); font-size: 20px;" data-original-title="Пригласите партнеров и друзей">
                <input type="text" class="form-control" placeholder="Email" name="email" id="email">
                <span class="input-group-btn">
                    <button type="button" class="btn btn-success btn-flat" id="inviteFriend"><i class="fa fa-paper-plane m-r-xxs" style="margin-top:-3px;"></i></button>
                </span>
            </div>
            </a>
        </form>
    </section>

</aside>
