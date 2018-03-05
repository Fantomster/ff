<div class="message_chat">
    <div class="clearfix">
        <span class="badge pull-right">
            <?= Yii::$app->formatter->asTime($time, "php:j M Y, H:i:s") ?>
        </span>
        <span class="name">
            <?= Yii::t('app', 'Отправитель') ?>: <?= ($isSystem ? '<span class="system" >System</span>' : $name) ?>
            <br>
            <?= Yii::t('app', 'Получатель') ?>:
            <?= $recipient->name ?>
            <span class="type">(<?= $recipient->type->name ?>)</span>
        </span>
    </div>
    <div class="text">
        <?= $message ?>
    </div>
</div>
