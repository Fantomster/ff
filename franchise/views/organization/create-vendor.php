<?php
$this->title = Yii::t('app', 'franchise.views.organization.add_vendor', ['ru'=>'Добавить поставщика']);
$organizationType = \common\models\Organization::TYPE_SUPPLIER;
$organization = $vendor;
?>
<section class="content-header">
    <h1>
        <i class="fa fa-home"></i> <?= Yii::t('app', 'franchise.views.organization.add_vendor_two', ['ru'=>'Добавить поставщика']) ?>
        <small><?= Yii::t('app', 'franchise.views.organization.new_info_two', ['ru'=>'Информация о новом клиенте']) ?></small>
    </h1>
</section>
<section class="content body">
    <div class="row">
        <div class="col-md-12">
<?= $this->render('_organization-form', compact('organization', 'user', 'profile', 'buisinessInfo', 'organizationType', 'managersArray')) ?>
        </div>
    </div>
</section>
