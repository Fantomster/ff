<?php if ($isSystem) { ?>
    <div class="direct-chat-msg system">
        <div class="direct-chat-info clearfix">
            <span class="direct-chat-name pull-left"><i>System</i></span>
            <span class="direct-chat-timestamp pull-right"><?= Yii::$app->formatter->asTime($time, "php:Y-m-d H:i:s") ?></span>
        </div>
        <!-- /.direct-chat-info -->
        <div class="direct-chat-text">
            <i><?= $message ?></i>
        </div>
        <!-- /.direct-chat-text -->
    </div>

<?php } else { ?>
    <div class="direct-chat-msg">
        <div class="direct-chat-info clearfix">
            <span class="direct-chat-name"><?= $name ?></span>
            <span class="direct-chat-timestamp"><?= Yii::$app->formatter->asTime($time, "php:Y-m-d H:i:s") ?></span>
        </div>
        <!-- /.direct-chat-info -->
        <div class="direct-chat-text">
            <?= $message ?>
        </div>
        <!-- /.direct-chat-text -->
    </div>
<?php } ?>