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
                        ['label' => 'Рабочий стол', 'icon' => 'home', 'url' => ['/client/index']],
                        [
                            'label' => 'Разместить заказ',
                            'icon' => 'opencart',
                            'url' => ['/order/create'],
                            'template' => '<a href="{url}">{icon}{label}<span class="pull-right-container"><span class="label label-primary pull-right cartCount">' . $cartCount . '</span></span></a>',
                        ],
                        [
                            'label' => 'Заказы',
                            'icon' => 'history',
                            'url' => ['/order/index'],
                            'template' => '<a href="{url}">{icon}{label}<span class="pull-right-container"><span class="label bg-yellow pull-right new-orders-count">' . ($newOrdersCount ? $newOrdersCount : '') . '</span></span></a>',
                        ],
                        ['label' => 'Поставщики', 'icon' => 'users', 'url' => ['/client/suppliers'], 'options' => ['class' => 'hidden-xs']],
//                        [
//                            'label' => 'Сообщения' . Html::tag('span', 4, ['class' => 'label label-danger pull-right']), 
//                            'icon' => 'envelope', 
//                            'url' => ['client/messages'],
//                            ],
                        ['label' => 'F-MARKET', 'icon' => 'shopping-cart', 'url' => 'https://market.f-keeper.ru', 'options' => ['class' => 'l-fmarket']],
                        ['label' => 'Заявки', 'icon' => 'paper-plane', 'url' => ['/request/list'], 'options' => ['class' => 'l-fmarket']],
                        ['label' => 'Аналитика', 'icon' => 'signal', 'url' => ['/client/analytics'], 'options' => ['class' => 'hidden-xs']],
                        ['label' => 'Обучающие видео', 'icon' => 'play-circle-o', 'url' => ['/client/tutorial', 'video' => 'video']],
                        // ['label' => 'Мои акции', 'icon' => 'fa fa-ticket', 'url' => ['client/events']],
                        ['label' => 'Новости', 'icon' => 'newspaper-o', 'url' => 'http://blog.f-keeper.ru?news', 'options' => ['class' => 'hidden-xs']],
                        [
                            'label' => 'Настройки',
                            'icon' => 'gears',
                            'url' => '#', //['client/settings'],
                            'options' => ['class' => "treeview hidden-xs"],
                            'items' => [
                                ['label' => 'Общие', 'icon' => 'circle-o', 'url' => ['/client/settings']],
                                //   ['label' => 'Интеграции', 'icon' => 'circle-o', 'url' => ['/clientintegr/default']],
                                ['label' => 'Сотрудники', 'icon' => 'circle-o', 'url' => ['/client/employees']],
                                ['label' => 'Уведомления', 'icon' => 'circle-o', 'url' => ['/settings/notifications']],
                            ]
                        ],
                        // ['label' => 'Поддержка', 'icon' => 'support', 'url' => ['client/support']],
                        ['label' => 'ОТПРАВИТЬ ПРИГЛАШЕНИЕ', 'options' => ['class' => 'header']],
                    ],
                ]
        )
        ?>
        <form action="<?= Url::to(['/user/ajax-invite-friend']) ?>" method="post" style="margin: 15px;" id="inviteForm">
            <div class="input-group input-group-sm" data-toggle="tooltip" data-placement="bottom" title="" style="color: rgb(255, 255, 255);font-size: 20px;" data-original-title="Пригласите партнеров и друзей">
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
