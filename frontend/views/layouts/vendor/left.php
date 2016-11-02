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
                        ['label' => 'Рабочий стол', 'icon' => 'fa fa-home', 'url' => ['vendor/index']],
                        ['label' => 'Заказы' . ($newOrdersCount ? Html::tag('span', $newOrdersCount, ['class' => 'label bg-yellow pull-right']) : ''), 'icon' => 'fa fa-history', 'url' => ['order/index']],
                        ['label' => 'Мои каталоги', 'icon' => 'fa fa-list-alt', 'url' => ['vendor/catalogs'],'options' =>['class' => 'hidden-xs']],
                        ['label' => 'Сообщения' . Html::tag('span', 4, ['class' => 'label label-danger pull-right']), 'icon' => 'fa fa-envelope', 'url' => ['vendor/messages']],
                        ['label' => 'Мои клиенты', 'icon' => 'fa fa-users', 'url' => ['vendor/clients']],
                        ['label' => 'Аналитика', 'icon' => 'fa fa-signal', 'url' => ['vendor/analytics'],'options' =>['class' => 'hidden-xs']],
                        //['label' => 'Обучающее видео', 'icon' => 'fa fa-play-circle-o', 'url' => ['vendor/tutorial']],
                        //['label' => 'Мои акции', 'icon' => 'fa fa-ticket', 'url' => ['vendor/events']],
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
                <input type="text" class="form-control" placeholder="Email" name="email">
                <span class="input-group-btn">
                    <button type="button" class="btn btn-success btn-flat" id="inviteFriend"><i class="fa fa-paper-plane m-r-xxs" style="margin-top:-3px;"></i></button>
                </span>
            </div>
        </form>
    </section>
</aside>
<?php
/*$sidebar_js = <<< JS
$(window).resize(function(){
$('#inviteForm').css('position','absolute').css('top',$(window).height()-60).removeClass('hide');    
}); $('#inviteForm').css('position','absolute').css('top',$(window).height()-60).removeClass('hide');         
JS;
$this->registerJs($sidebar_js, View::POS_READY);*/
?>
