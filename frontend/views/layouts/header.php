<?php

use yii\helpers\Html;
use yii\helpers\Url;
use common\models\Organization;

/* @var $this \yii\web\View */
/* @var $content string */
if (!Yii::$app->user->isGuest) {
    $user = Yii::$app->user->identity;
    $organization = $user->organization;
    $homeUrl = Yii::$app->urlManager->baseUrl;
    $js = <<<JS

   socket = io.connect('http://$homeUrl:8890');

   socket.on('connect', function(){
        socket.emit('authentication', {userid: "$user->id", token: "$user->access_token"});
    });
    socket.on('user$user->id', function (data) {

        var message = JSON.parse(data);

        messageBody = $.parseHTML( message.body );
        
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
JS;
    $this->registerJs($js, \yii\web\View::POS_READY)
    ?>
    <script type="text/javascript">
        var socket;
        var dataEdited = 0;
        var link = '#';
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
                    <li class="dropdown messages-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <i class="fa fa-envelope-o"></i>
                            <span class="label label-danger">4</span>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="header">You have 4 messages</li>
                            <li>
                                <!-- inner menu: contains the actual data -->
                                <ul class="menu">
                                    <li><!-- start message -->
                                        <a href="#">
                                            <div class="pull-left">
                                                <img src="<?= $directoryAsset ?>/img/user2-160x160.jpg" class="img-circle"
                                                     alt="User Image"/>
                                            </div>
                                            <h4>
                                                Support Team
                                                <small><i class="fa fa-clock-o"></i> 5 mins</small>
                                            </h4>
                                            <p>Why not buy a new awesome theme?</p>
                                        </a>
                                    </li>
                                    <!-- end message -->
                                    <li>
                                        <a href="#">
                                            <div class="pull-left">
                                                <img src="<?= $directoryAsset ?>/img/user3-128x128.jpg" class="img-circle"
                                                     alt="user image"/>
                                            </div>
                                            <h4>
                                                AdminLTE Design Team
                                                <small><i class="fa fa-clock-o"></i> 2 hours</small>
                                            </h4>
                                            <p>Why not buy a new awesome theme?</p>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#">
                                            <div class="pull-left">
                                                <img src="<?= $directoryAsset ?>/img/user4-128x128.jpg" class="img-circle"
                                                     alt="user image"/>
                                            </div>
                                            <h4>
                                                Developers
                                                <small><i class="fa fa-clock-o"></i> Today</small>
                                            </h4>
                                            <p>Why not buy a new awesome theme?</p>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#">
                                            <div class="pull-left">
                                                <img src="<?= $directoryAsset ?>/img/user3-128x128.jpg" class="img-circle"
                                                     alt="user image"/>
                                            </div>
                                            <h4>
                                                Sales Department
                                                <small><i class="fa fa-clock-o"></i> Yesterday</small>
                                            </h4>
                                            <p>Why not buy a new awesome theme?</p>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#">
                                            <div class="pull-left">
                                                <img src="<?= $directoryAsset ?>/img/user4-128x128.jpg" class="img-circle"
                                                     alt="user image"/>
                                            </div>
                                            <h4>
                                                Reviewers
                                                <small><i class="fa fa-clock-o"></i> 2 days</small>
                                            </h4>
                                            <p>Why not buy a new awesome theme?</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li class="footer"><a href="#">See All Messages</a></li>
                        </ul>
                    </li>
                    <li class="dropdown notifications-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <i class="fa fa-bell-o"></i>
                            <span class="label label-warning">10</span>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="header">You have 10 notifications</li>
                            <li>
                                <!-- inner menu: contains the actual data -->
                                <ul class="menu">
                                    <li>
                                        <a href="#">
                                            <i class="fa fa-users text-aqua"></i> 5 new members joined today
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#">
                                            <i class="fa fa-warning text-yellow"></i> Very long description here that may
                                            not fit into the page and may cause design problems
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#">
                                            <i class="fa fa-users text-red"></i> 5 new members joined
                                        </a>
                                    </li>

                                    <li>
                                        <a href="#">
                                            <i class="fa fa-shopping-cart text-green"></i> 25 sales made
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#">
                                            <i class="fa fa-user text-red"></i> You changed your username
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li class="footer"><a href="#">View all</a></li>
                        </ul>
                    </li>
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
                    <!-- User Account: style can be found in dropdown.less -->

                    <!--                    <li class="dropdown user user-menu">
                                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                                <img src="<?= $directoryAsset ?>/img/user2-160x160.jpg" class="user-image" alt="User Image"/>
                                                <span class="hidden-xs"><?= Yii::$app->user->identity->profile->full_name ?></span>
                                            </a>
                                            <ul class="dropdown-menu">
                                                 User image 
                                                <li class="user-header">
                                                    <img src="<?= $directoryAsset ?>/img/user2-160x160.jpg" class="img-circle"
                                                         alt="User Image"/>
                    
                                                    <p>
                    <?= Yii::$app->user->identity->profile->full_name ?>
                                                        <small><?= Yii::$app->user->identity->role->name ?></small>
                                                    </p>
                                                </li>
                                                 Menu Footer
                                                <li class="user-footer">
                                                    <div class="pull-left">
                                                        <a href="#" class="btn btn-default btn-flat">Profile</a>
                                                    </div>
                                                    <div class="pull-right">
                    <?=
                    Html::a(
                            'Logout', ['/user/logout'], ['data-method' => 'post', 'class' => 'btn btn-default btn-flat']
                    )
                    ?>
                                                    </div>
                                                </li>
                                            </ul>
                                        </li>-->

                    <!-- User Account: style can be found in dropdown.less -->
                </ul>
            </div>
        <?php } ?>
    </nav>
</header>
