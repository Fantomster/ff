<?php

use yii\helpers\Html;
use yii\web\View;
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
                        [
                            'label' => 'Разместить заказ',
                            'icon' => 'fa fa-opencart',
                            'url' => ['order/create'],
                            'template' => '<a href="{url}">{icon}{label}<span class="pull-right-container"><span class="label label-primary pull-right cartCount">'.$cartCount.'</span></span></a>',
                        ],
                        [
                            'label' => 'Заказы',
                            'icon' => 'fa fa-history',
                            'url' => ['order/index'],
                            'template' => '<a href="{url}">{icon}{label}<span class="pull-right-container"><span class="label bg-yellow pull-right">'.($newOrdersCount ? $newOrdersCount : '').'</span></span></a>',
                        ],
                        ['label' => 'Поставщики', 'icon' => 'fa fa-users', 'url' => ['client/suppliers'], 'options' => ['class' => 'hidden-xs']],
//                        [
//                            'label' => 'Сообщения' . Html::tag('span', 4, ['class' => 'label label-danger pull-right']), 
//                            'icon' => 'fa fa-envelope', 
//                            'url' => ['client/messages'],
//                            ],
                        ['label' => 'Аналитика', 'icon' => 'fa fa-signal', 'url' => ['client/analytics'], 'options' => ['class' => 'hidden-xs']],
                        // ['label' => 'Обучающее видео', 'icon' => 'fa fa-play-circle-o', 'url' => ['client/tutorial']],
                        // ['label' => 'Мои акции', 'icon' => 'fa fa-ticket', 'url' => ['client/events']],
                        [
                            'label' => 'Настройки',
                            'icon' => 'fa fa-gears',
                            'url' => '#', //['client/settings'],
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
            <div class="input-group input-group-sm" data-toggle="tooltip" data-placement="top" title="" style="color: rgb(255, 255, 255);font-size: 20px;" data-original-title="Пригласите партнеров и друзей">
                <input type="text" class="form-control" placeholder="Email">
                <span class="input-group-btn">
                    <button type="button" class="btn btn-success btn-flat" id="inviteFriend"><i class="fa fa-paper-plane m-r-xxs" style="margin-top:-3px;"></i></button>
                </span>
            </div>
        </form>
    </section>

</aside>
<?php
$sidebar_js = <<< JS
$(window).resize(function(){
$('#inviteForm').css('position','absolute').css('top',$(window).height()-60).removeClass('hide');    
}); $('#inviteForm').css('position','absolute').css('top',$(window).height()-60).removeClass('hide');        
JS;
$this->registerJs($sidebar_js, View::POS_READY);
?>        
