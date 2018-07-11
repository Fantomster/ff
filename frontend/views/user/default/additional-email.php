<div class="main-page-wrapper <?php
if ($success) {
    echo "success";
}
?>">
<?php if ($success): ?>
        <div class="success-message"><a href="<?= Yii::$app->params['staticUrl'][Yii::$app->language]['home'] ?>" class="success-message__ico"></a>
            <div class="success-message__text">
                <p><?= Yii::t("app", 'frontend.views.user.default.additional_email_confirmed', ["ru" => "Дополнительная почта подтверждена!"]) ?></p>
            </div>
        </div>

<?php else: ?>

        <div class="success-message"><a href="<?= Yii::$app->params['staticUrl'][Yii::$app->language]['home'] ?>" class="success-message__ico"></a>
            <div class="success-message__text">
                <p><?= Yii::t('app', 'frontend.views.user.default.incorrect_url', ['ru'=>'Некорректная ссылка']) ?></p>
            </div>
        </div>

<?php endif; ?>
    <div class="present-wrapper">
        <button type="button" class="close-menu-but visible-xs visible-sm visible-md"><span></span><span></span></button>
        <h1><?= Yii::t('message', 'frontend.views.user.default.auto', ['ru'=>'Онлайн-сервис для автоматизации закупок']) ?></h1>
        <div class="present__media clearfix">
            <div class="present__image"><img src="<?= Yii::$app->urlManagerFrontend->baseUrl ?>/images/tmp_file/flowers.png" alt=""></div>
        </div>
    </div>
</div>