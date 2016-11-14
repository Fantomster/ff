<?php

use yii\helpers\Html;
use yii\helpers\Url;
use common\models\Organization;

kartik\growl\GrowlAsset::register($this);

/* @var $this \yii\web\View */
/* @var $content string */
if (!Yii::$app->user->isGuest) {
    $user = Yii::$app->user->identity;
    $organization = $user->organization;
    $homeUrl = parse_url(Url::base(true), PHP_URL_HOST);
    $notificationsUrl = isset(Yii::$app->params['notificationsUrl']) ? Yii::$app->params['notificationsUrl'] : "http://$homeUrl:8890";
    //Yii::$app->urlManager->baseUrl;
    $refreshStatsUrl = Url::to(['order/ajax-refresh-stats']);
    $unreadMessages = $organization->unreadMessages;
    $unreadNotifications = $organization->unreadNotifications;
    $js = <<<JS

   socket = io.connect('$notificationsUrl');

   socket.on('connect', function(){
        socket.emit('authentication', {userid: "$user->id", token: "$user->access_token"});
    });
    socket.on('user$user->id', function (data) {

        var message = JSON.parse(data);

        messageBody = $.parseHTML( message.body );
        
        orderId = $("#order_id").val();
        if (orderId == message.order_id) {
            $( ".direct-chat-messages" ).prepend( message.body );
            senderId = $("#sender_id").val();
            messageWrapper = $("#msg" + message.id);
            if (senderId == message.sender_id) {
                messageWrapper.addClass("right");
                messageWrapper.find(".direct-chat-name").removeClass("pull-left").addClass("pull-right");
                messageWrapper.find(".direct-chat-timestamp").removeClass("pull-right").addClass("pull-left");
            } else {
                messageWrapper.find(".direct-chat-name").removeClass("pull-right").addClass("pull-left");
                messageWrapper.find(".direct-chat-timestamp").removeClass("pull-left").addClass("pull-right");
            }
        }
        if (message.isSystem) {
            if (message.isSystem == 1) {
                form = $("#actionButtonsForm");
                $.post(
                    form.attr("action"),
                    form.serialize()
                ).done(function(result) {
                    $('#actionButtons').html(result);
                    $.pjax.reload({container: "#orderContent"});
                });
            } else if (message.isSystem == 2) {
                $(".cartCount").html(message.body);
                try {
                    $.pjax.reload({container: "#checkout"});
                } catch(e) {
                }
            }
        }
            
        $.get(
            '$refreshStatsUrl'
        ).done(function(result) {
            if (result.unreadMessagesCount > 0) {
                $(".unread-messages-count").show();
            } else {
                $(".unread-messages-count").hide();
            }
            if (result.unreadNotificationsCount > 0) {
                $(".unread-notifications-count").show();
            } else {
                $(".unread-notifications-count").hide();
            }
            $(".unread-messages-count").html(result.unreadMessagesCount);
            $(".unread-notifications-count").html(result.unreadNotificationsCount);
            $(".new-orders-count").html(result.newOrdersCount);
            $(".unread-messages").html(result.unreadMessages);
            $(".unread-notifications").html(result.unreadNotifications);
        });
            

    });
        
$('#chat-form').submit(function() {

     var form = $(this);

     $.ajax({
          url: form.attr('action'),
          type: 'post',
          data: form.serialize(),
          success: function (response) {
               $("#message-field").val("");
          }
     });

     return false;
});
            
            $(document).on("submit", "#inviteForm", function(e) {
                e.preventDefault();
                form = $("#inviteForm");
                $.post(
                    form.attr("action"),
                    form.serialize()
                ).done(function(result) {
                    $("#email").val('');
                    if (result.success) {
                        $.notify(result.growl.options, result.growl.settings);
                    }
                });
            });
JS;
    $this->registerJs($js, \yii\web\View::POS_READY)
    ?>
    <script type="text/javascript">
        var socket;
        var dataEdited = 0;
        var link = '#';
        var timer = null;
    </script>
<?php } ?>
<header class="main-header">

    <?= Html::a('<span class="logo-mini"><b>f</b>k</span><span class="logo-lg"><b>f</b>-keeper</span>', Yii::$app->homeUrl, ['class' => 'logo']) ?>

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
                    <?php if ($organization->type_id == Organization::TYPE_RESTAURANT) { ?>
                        <li>
                            <a href="<?= Url::to(['order/checkout']) ?>">
                                <i class="fa fa-shopping-cart"></i><span class="label label-primary cartCount"><?= $organization->getCartCount() ?></span>
                            </a>
                        </li>
                    <?php } ?>
                    <!-- Messages: style can be found in dropdown.less-->
                    <?php //if (false) { ?>
                    <li class="dropdown messages-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <i class="fa fa-envelope-o"></i>
                            <span class="label label-danger unread-messages-count" style="display: <?= count($unreadMessages) ? 'block' : 'none'?>"><?= count($unreadMessages) ?></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="header">Непрочитанных сообщений: <span class="unread-messages-count"><?= count($unreadMessages) ?></span></li>
                            <li>
                                <!-- inner menu: contains the actual data -->
                                <ul class="menu unread-messages">
                                    <?php
                                        foreach ($unreadMessages as $message) {
                                            echo $this->render('/order/_header-message', compact('message'));
                                        }
                                    ?>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <li class="dropdown messages-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <i class="fa fa-bell-o"></i>
                            <span class="label label-warning unread-notifications-count" style="display: <?= count($unreadNotifications) ? 'block' : 'none'?>"><?= count($unreadNotifications) ?></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="header">Оповещений: <span class="unread-notifications-count"><?= count($unreadNotifications) ?></span></li>
                            <li>
                                <!-- inner menu: contains the actual data -->
                                <ul class="menu unread-notifications">
                                    <?php
                                        foreach ($unreadNotifications as $message) {
                                            echo $this->render('/order/_header-message', compact('message'));
                                        }
                                    ?>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <?php //} ?>
                    <!-- Tasks: style can be found in dropdown.less -->
                    <li class="dropdown user user-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                            <img src="images/no-avatar.jpg" class="user-image" alt="User Image">
                            <span class="hidden-xs"><?= $user->profile->full_name ?></span>
                        </a>
                        <ul class="dropdown-menu">
                            <!-- User image -->
                            <li class="user-header">
                                <img src="images/no-avatar.jpg" class="img-circle" alt="User Image">

                                <p>
                                    <?= $user->profile->full_name ?> - <?= $user->role->name ?>
                                    <small><?= $user->email ?></small>
                                    <small><?= $organization->name ?></small>
                                </p>
                            </li>
                            <!-- Menu Body -->

                            <!-- Menu Footer-->

                        </ul>
                    </li>
                    <li class="dropdown tasks-menu">
                        <?=
                        Html::a(
                                '<i class="fa fa-sign-out"></i> Выход', ['/user/logout'], ['data-method' => 'post']
                        )
                        ?>
                    </li>

                </ul>
            </div>
        <?php } ?>
    </nav>
</header>
