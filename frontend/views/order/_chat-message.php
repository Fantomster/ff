<?php
if(is_a(Yii::$app,'yii\console\Application')){
    $isSender = 1;
}else{
    $isSender = (Yii::$app->user->identity->id == $sender_id) && !$ajax;
}
?>
<?php if ($isSystem) { ?>
    <div class="direct-chat-msg <?= $danger? "system-danger" : "system" ?>">
        <div class="direct-chat-info clearfix">
            <span class="direct-chat-name pull-left"><i>System</i></span>
            <span class="direct-chat-timestamp pull-right"><?= Yii::$app->formatter->asTime($time, "php:j M Y, H:i:s") ?></span>
        </div>
        <div class="direct-chat-text container-fluid">
            <div>
                <i><?= str_replace("{ul_style}", "display:list-item;margin-bottom:0;", str_replace("{li_style}", "margin-left:20px;", $message)) ?></i>
            </div>
        </div>
    </div>

<?php } else { ?>
    <div class="direct-chat-msg<?= $isSender ? " right" : "" ?>" id="msg<?= $id ?>">
        <div class="direct-chat-info clearfix">
            <span class="direct-chat-name<?= $isSender ? " pull-right" : " pull-left" ?>"><?= $name ?></span>
            <span class="direct-chat-timestamp<?= $isSender ? " pull-left" : " pull-right" ?>"><?= Yii::$app->formatter->asTime($time, "php:j M Y, H:i:s") ?></span>
        </div>
        <div class="direct-chat-text">
            <span><?= str_replace("{ul_style}", "display:list-item;margin-bottom:0;", str_replace("{li_style}", "margin-left:20px;", $message)) ?></span>
        </div>
    </div>
<?php } ?>