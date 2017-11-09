<?php
$this->title = Yii::t('app', 'Добавить поставщика');
$organizationType = \common\models\Organization::TYPE_SUPPLIER;
$organization = $vendor;
?>
<section class="content-header">
    <h1>
        <i class="fa fa-home"></i> <?= Yii::t('app', 'Добавить поставщика') ?>
        <small><?= Yii::t('app', 'Информация о новом клиенте') ?></small>
    </h1>
</section>
<section class="content body">
    <div class="row">
        <div class="col-md-12">
<?= $this->render('_organization-form', compact('organization', 'user', 'profile', 'buisinessInfo', 'organizationType', 'managersArray')) ?>
        </div>
    </div>
</section>
