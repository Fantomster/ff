<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use common\models\Organization;
use common\models\Role;
use yii\widgets\Pjax;

/* @var $this \yii\web\View */
/* @var $content string */
if (!Yii::$app->user->isGuest) {
    $user = Yii::$app->user->identity;
    $organization = $user->organization;
    $profile = $user->profile;
    $homeUrl = parse_url(Url::base(true), PHP_URL_HOST);
    $cartUrl = Url::to(['/order/pjax-cart']);
    $notificationsUrl = isset(Yii::$app->params['notificationsUrl']) ? Yii::$app->params['notificationsUrl'] : "http://$homeUrl:8890";
    $refreshStatsUrl = Url::to(['/order/ajax-refresh-stats']);
    $tutorialOn = Url::to(['/site/ajax-tutorial-on']);
    $dashboard = Url::to(['/site/index']);
    $unreadMessages = $organization->unreadMessages;
    $unreadNotifications = $organization->unreadNotifications;
    $changeNetworkUrl = Yii::$app->urlManager->createAbsoluteUrl(['/user/change']);
    $changeFormUrl = Url::to(['/user/default/change-form']);

    $arr = [
        Yii::t('message', 'frontend.views.layouts.header.var1', ['ru' => 'Приглашение на MixCart']),
        Yii::t('message', 'frontend.views.layouts.header.var2', ['ru' => 'Отмена']),
        Yii::t('message', 'frontend.views.layouts.header.var3', ['ru' => 'Отправить']),
        Yii::t('message', 'frontend.views.layouts.header.var4', ['ru' => 'Некорректный email!']),
        Yii::t('message', 'frontend.views.layouts.header.var5', ['ru' => 'Приглашение отправлено!']),
        Yii::t('error', 'frontend.views.layouts.header.var6', ['ru' => 'Ошибка!']),
        Yii::t('message', 'frontend.views.layouts.header.var7', ['ru' => 'Попробуйте еще раз']),
        Yii::t('message', 'frontend.views.layouts.header.var8', ['ru' => 'Непрочитанных сообщений:']),
    ];



    $js = <<<JS
    

    socket = io.connect('$notificationsUrl');

    function refreshMenu(result) {
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
    }
            
   socket.on('connect', function(){
                
        socket.emit('authentication', {userid: "$user->id", token: "$user->access_token"});
        
    });
    socket.on('user$user->id', function (data) {

        var message = JSON.parse(data);

        messageBody = $.parseHTML( message.body );
            
        orderId = $("#order_id").val();
        if (orderId == message.order_id) {
            $( "#chatBody" ).append( message.body );
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
            try {
                $("#chatBody").scrollTop($("#chatBody")[0].scrollHeight);
            } catch(e) {
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
                    if (!saving) {
                        try {
                            $.pjax.reload({container: "#orderContent",timeout:30000});
                        } catch(e) {
                        }
                    }
                });
            } else if (message.isSystem == 2) {
                $(".cartCount").html(message.body);
            }
        }

        
        if (message.isRabbit == 1) {
            if (message.action == 'fullmap') {
                
                $('#fullmapconsole').show();
                $('#fullmapbutton').hide();
                
                $('#fmtotal').progressTo(Math.round((message.success + message.failed)*100/message.total))
                $('#fmsuccess').progressTo(Math.round(message.success*100/message.total));
                $('#fmfailed').progressTo(Math.round(message.failed*100/message.total));
                
                $('#fmtotal_dig').text(message.total);
                $('#fmsuccess_dig').text(message.success);
                $('#fmfailed_dig').text(message.failed);
                
                if(message.total == (message.success + message.failed)) //Все обработано
                {
                    $.pjax.reload("#map_grid1", {timeout:30000});
                }
            }
        }

        if (message.isSystem) {
        $.get(
            '$refreshStatsUrl'
        ).done(function(result) {
            refreshMenu(result);
        });
        }
    });
            
    $(document).on("pjax:complete", "#checkout", function() {
        $("#"+activeCart).addClass("active");
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
        swal({
            title: "$arr[0]",
            input: "text",
            showCancelButton: true,
            cancelButtonText: "$arr[1]",
            confirmButtonText: "$arr[2]",
            showLoaderOnConfirm: true,
            allowOutsideClick: false,
            showLoaderOnConfirm: true,
            inputValue: $("#email").val(),
            inputValidator: function (value) {
                return new Promise(function (resolve, reject) {
                    if (email) {
                        resolve();
                    } else {
                        reject('$arr[3]');
                    }
                })
            },
            preConfirm: function (email) {
                return new Promise(function (resolve, reject) {
                    $.post(
                        form.attr("action"),
                        {email: email}
                    ).done(function(result) {
                        $("#email").val('');
                        if (result) {
                            resolve(result);
                        } else {
                            resolve(false);
                        }
                    });
                })
            },
        }).then(function (result) {
            if (result.value.success) {
                swal({title: "$arr[4]", type: "success"});
            } else if (result.value.dismiss === "cancel") {
                swal.close();
            } else {
                swal({title: "$arr[5]", text: "$arr[6]", type: "error"});
            }
        });            
    });
            
    $(document).on("click", ".setRead", function(e) {
        e.preventDefault();
        $.get(
            $(this).data("url")
        ).done(function(result) {
            refreshMenu(result);
            $(this).setAttribute('style','visibility: hidden;');
        });
    });
            
    $(document).on("click", ".repeat-tutorial", function(e) {
            e.preventDefault();
            $.get(
                '$tutorialOn'
            ).done(function(result) {
                if (result) {
                    document.location = "$dashboard";
                }
            });
    });
            
$("body").on("hidden.bs.modal", "#changeNetOrg", function() {
    $(this).data("bs.modal", null);
})
$(document).on("click", "#change_business", function(e) {
    e.preventDefault();
    $("#changeNetOrg").load("$changeFormUrl", function(result) {
        $('#changeBusinessModal').modal({show:true, top: "20%"});
    });
});
$(document).on("click",".change-net-org", function(e){
    e.preventDefault();
    var id = $(this).attr('data-id'); 
    $.get(
        '$changeNetworkUrl',
        {id : id}
    ).done(function(result) {
        if (result) {
            document.location = "$dashboard";
        }
    });
})
$(document).on("click", ".new-network", function(e) { 
    e.preventDefault();        
    var form = $("#create-network-form");
    $.ajax({
        url: form.attr('action'),
        type: 'post',
        data: form.serialize(),
        success: function (response) {  
          $.pjax.reload({container: '#pjax-network-list', push:false, replace:false, timeout:30000, async: false, url: "$changeFormUrl"});
          $("#create-network-form")[0].reset();
        },
        error: function(jqXHR, errMsg) { 
            // handle error
        }
    });
    return false;
});      
JS;
    $this->registerJs($js, \yii\web\View::POS_READY)
    ?>
    <?php $this->registerCss("
::-webkit-scrollbar {
    width: 6px;
}
::-webkit-scrollbar-track {
    background-color: #fff;
    border-left: 1px solid #eee;
}
::-webkit-scrollbar-thumb {
    border-radius:4px;
    background-color: #84bf76;
}
::-webkit-scrollbar-thumb:hover {
  background-color: #88bd36;
}

    #changeBusinessModal .modal-content {
        margin-top: 20%;
    }
    .network-modal{
        padding:20px 30px 20px 30px;
    }
    .network-modal h5, .network-modal h4, .network-modal h3, .network-modal h2{
     font-family: 'Circe-Bold';  
     letter-spacing: 0.05em;
    }
    .network-modal h3, .network-modal h3 span{
     font-family: 'Circe-Bold';
     letter-spacing: 0.05em;
     font-size:28px;
    }
    .network-modal a, .network-modal div, .network-modal p, .network-modal span{
     font-family: 'Circe-Regular';
     letter-spacing: 0.05em;
     font-size:14px;
    }
    .network-modal .network-list{
        overflow-y: auto;
    }
    .network-modal .new-network{
        height:40px;
        border-radius: 50px;
        font-size: 19px;
        width:100%;
        margin-top:20px;
        
    }
    #changeNetOrg .modal-content{
        background-color:rgba(255, 255, 255, 0);
    }
    .network-modal{
        border-radius:4px;
    }
    .btn-business{
    background-color: #fff;
    border-radius: 4px;
    font-size: 14px;
    box-shadow: 0,0,10px rgba(0,0,0, 0.4);
    box-shadow: 0 0 6px rgba(0,0,0,0.3);
    width:100%;
    text-align:center;
    }
");
    ?>
    <script type="text/javascript">
        var socket;
        var dataEdited = 0;
        var link = '#';
        var timer = null;
        var saving = false;
        var activeCart;
    </script>
<?php } ?>
<header class="main-header">
    <?= Html::a('<span class="logo-mini"><b>M</b>C</span><span class="logo-lg">MixCart</span>', Yii::$app->homeUrl, ['class' => 'logo']) ?>

    <nav class="navbar navbar-static-top" role="navigation">
        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
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
                            <a class="basket_a" href="<?= Url::to(['/order/checkout']) ?>">
                                <i class="fa fa-shopping-cart"></i><span class="label label-primary cartCount"><?= $organization->getCartCount() ?></span>
                            </a>
                        </li>
                    <?php } ?>
                    <!-- Messages: style can be found in dropdown.less-->
                    <?php //if (false) { ?>
                    <li class="dropdown messages-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <i class="fa fa-envelope-o"></i>
                            <span class="label label-danger unread-messages-count" style="display: <?= count($unreadMessages) ? 'block' : 'none' ?>"><?= count($unreadMessages) ?></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="header"><?= Yii::t('message', 'frontend.views.layouts.header.unread', ['ru' => 'Непрочитанных сообщений:']) ?> <span class="unread-messages-count"><?= count($unreadMessages) ?></span></li>
                            <li>
                                <!-- inner menu: contains the actual data -->
                                <ul class="menu unread-messages">
                                    <?php
                                    foreach ($unreadMessages as $message) {
                                        echo $this->render('@frontend/views/order/_header-message', compact('message'));
                                    }
                                    ?>
                                </ul>
                            </li>
                            <?php if((count($unreadMessages) > 0))
                                {
                                    ?>
                            <li class="footer">
                                <a href="#" class="setRead" data-url="<?= Url::to(['/order/ajax-refresh-stats', 'setMessagesRead' => 1]); ?>"><?= Yii::t('message', 'frontend.views.layouts.header.check_as_read', ['ru' => 'Пометить как прочитанные']) ?></a>
                            </li>
                          <?php  } ?>
                        </ul>
                    </li>
                    <li class="dropdown messages-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <i class="fa fa-bell-o"></i>
                            <span class="label label-warning unread-notifications-count" style="display: <?= count($unreadNotifications) ? 'block' : 'none' ?>"><?= count($unreadNotifications) ?></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="header"><?= Yii::t('message', 'frontend.views.layouts.header.messages', ['ru' => 'Оповещений:']) ?> <span class="unread-notifications-count"><?= count($unreadNotifications) ?></span></li>
                            <li>
                                <!-- inner menu: contains the actual data -->
                                <ul class="menu unread-notifications">
                                    <?php
                                    foreach ($unreadNotifications as $message) {
                                        echo $this->render('@frontend/views/order/_header-message', compact('message'));
                                    }
                                    ?>
                                </ul>
                            </li>
                            <?php if (count($unreadNotifications) > 0) { ?>
                            <li class="footer">
                                <a href="#" class="setRead" data-url="<?= Url::to(['/order/ajax-refresh-stats', 'setNotificationsRead' => 1]); ?>"><?= Yii::t('message', 'frontend.views.layouts.header.check_as_read_two', ['ru' => 'Пометить как прочитанные']) ?></a>
                            </li>
                            <?php } ?>
                        </ul>
                    </li>
                    <?php if ($organization->type_id == Organization::TYPE_RESTAURANT) { ?>
                        <li data-toggle="tooltip" data-placement="bottom" data-original-title="<?= Yii::t('message', 'frontend.views.layouts.header.repeat_learning', ['ru' => 'Повторить обучение']) ?>">
                            <a href="#" class="repeat-tutorial">
                                <i class="fa fa-question-circle"></i>
                            </a>
                        </li>
                    <?php } ?>
                    <!-- Tasks: style can be found in dropdown.less -->
                    <li class="dropdown user user-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                            <img src="<?= $user->profile->miniAvatarUrl ?? '' ?>" class="user-image avatar" alt="User Image">
                            <span class="hidden-xs"><?= $user->profile->full_name ?? '' ?></span>
                        </a>
                        <ul class="dropdown-menu">
                            <!-- User image -->
                            <li class="user-header">
                                <img src="<?= $user->profile->avatarUrl ?? '' ?>" class="img-circle avatar" alt="User Image">

                                <p>
                                    <?= empty($user->profile->full_name) ? '&nbsp;' : $user->profile->full_name ?> - <?= Yii::t('app', $user->role->name) ?>
                                    <small><?= $user->email ?></small>
                                    <small><?= $organization->name ?></small>
                                </p>
                                <?php
                                if ($user->status == \common\models\User::STATUS_ACTIVE && ($user->role_id == Role::ROLE_RESTAURANT_MANAGER ||
                                        $user->role_id == Role::ROLE_SUPPLIER_MANAGER ||
                                        $user->role_id == Role::ROLE_ADMIN ||
                                        $user->role_id == Role::ROLE_FKEEPER_MANAGER ||
                                        in_array($user->role_id, Role::getFranchiseeEditorRoles())) || \common\models\RelationUserOrganization::checkRelationExisting($user)) {
                                    echo Html::a(Yii::t('message', 'frontend.views.layouts.header.businesses', ['ru' => "БИЗНЕСЫ"]), "#", [
                                        'id' => 'change_business',
                                        'class' => 'btn btn-lg btn-business',
                                    ]);
                                }
                                ?>
                            </li>
                            <!--li class="user-body" style="padding:0;border:0;">
                               <span class="btn btn-lg btn-gray" style="border-radius:0;width:100%;text-align:center;">смена бизнеса</span> 
                            </li-->
                            <!-- Menu Body -->

                            <!-- Menu Footer-->

                        </ul>
                    </li>
                    <?= \common\widgets\LangSwitch::widget(); ?>
                    <li class="dropdown tasks-menu">
                        <?= Html::a('<i class="fa fa-sign-out"></i> ' . Yii::t('message', 'frontend.views.layouts.header.exit', ['ru' => 'Выход']), ['/user/logout'], ['data-method' => 'post']) ?>
                    </li>

                </ul>
            </div>
        <?php } ?>
    </nav>
</header>
    <div id="changeNetOrg"></div>
<?= ''
//Modal::widget([
//    'id' => 'changeNetOrg',
//    'size' => 'modal-lg',
//    'clientOptions' => false
//])
?>