<?php

use yii\helpers\Html;
use yii\helpers\Url;

if (!Yii::$app->user->isGuest) {
    $user = Yii::$app->user->identity;
}
?>
<header class="main-header">

    <?= Html::a('<span class="logo-mini"><b>m</b>c</span><span class="logo-lg"><b>mix</b>-cart</span>', Yii::$app->homeUrl, ['class' => 'logo']) ?>

    <nav class="navbar navbar-static-top" role="navigation">
        <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </a>
        <?php if (Yii::$app->user->isGuest) { ?>
            <div class="navbar-custom-menu">
                <ul class="nav navbar-nav">
                    <li>
                        <?=
                        Html::a(
                                'Login', ['/user/login']
                        )
                        ?>
                    </li>
                </ul>   
            </div>
        <?php } else { ?>
            <div class="navbar-custom-menu">

                <ul class="nav navbar-nav">
                    <li class="dropdown messages-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <i class="fa fa-bell-o"></i>
                            <span class="label label-warning unread-notifications-count" style="display: <?= false ? 'block' : 'none' ?>"><?= 0 ?></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="header"><?= Yii::t('app', 'franchise.views.layouts.messages_two', ['ru'=>'Оповещений:']) ?> <span class="unread-notifications-count"><?= 0 ?></span></li>
                            <li>
                                <!-- inner menu: contains the actual data -->
                                <ul class="menu unread-notifications">
                                    <?php
//                                        foreach ($unreadNotifications as $message) {
//                                            echo $this->render('/order/_header-message', compact('message'));
//                                        }
                                    ?>
                                </ul>
                            </li>
                            <li class="footer">
                                <a href="#" class="setRead" data-msg="0" data-ntf="1"><?= Yii::t('app', 'franchise.views.layouts.check_as_read_two', ['ru'=>'Пометить как прочитанные']) ?></a>
                            </li>
                        </ul>
                    </li>
                    <?php //} ?>
                    <!-- Tasks: style can be found in dropdown.less -->
                    <li class="dropdown user user-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                            <img src="<?= $user->profile->miniAvatarUrl ?>" class="user-image avatar" alt="User Image">
                            <span class="hidden-xs"><?= $user->profile->full_name ?></span>
                        </a>
                        <ul class="dropdown-menu">
                            <!-- User image -->
                            <li class="user-header">
                                <img src="<?= $user->profile->avatarUrl ?>" class="img-circle avatar" alt="User Image">

                                <p>
                                    <?= $user->profile->full_name ?> - <?= $user->role->name ?>
                                    <small><?= $user->email ?></small>
                                    <small><?= ''//$organization->name  ?></small>
                                </p>
                            </li>
                            <!-- Menu Body -->

                            <!-- Menu Footer-->

                        </ul>
                    </li>
                    <?=\common\widgets\LangSwitch::widget();?>
                    <li class="dropdown tasks-menu">
                        <?=
                        Html::a(
                                '<i class="fa fa-sign-out"></i> ' . Yii::t('app', 'franchise.views.layouts.exit_two', ['ru'=>'Выход']) . ' ', ['/user/logout'], ['data-method' => 'post']
                        )
                        ?>
                    </li>

                </ul>
            </div>
        <?php } ?>
    </nav>
</header>
