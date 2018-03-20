<?php
$menu = [
    [
        'url' => \yii\helpers\Url::to(['/clientintegr/email/invoice']),
        'title' => 'Накладные',
        'active' => in_array(\Yii::$app->controller->id, ['invoice'])
    ],
    [
        'url' => \yii\helpers\Url::to(['/clientintegr/email/setting']),
        'title' => 'Настройки почтовых серверов',
        'active' => in_array(\Yii::$app->controller->id, ['setting'])
    ]
];

?>
<div class="btn-group">
    <?php foreach ($menu as $item): ?>
        <a href="<?= $item['url'] ?>" class="btn btn-primary <?= ($item['active'] ? 'active' : '') ?>">
            <?= $item['title'] ?>
        </a>
    <?php endforeach; ?>
</div>