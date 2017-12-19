<?php

$organizationType = \common\models\Organization::TYPE_SUPPLIER;
$organization = $vendor;
?>
<section class="content-header">
    <h1>
        <i class="fa fa-home"></i> <?= Yii::t('app', 'franchise.views.organization.change_vendor_info', ['ru'=>'Изменить информацию о поставщике']) ?> <?= $organization->name ?>
        <small><?= Yii::t('app', 'franchise.views.organization.edit_four', ['ru'=>'Редактирование информации о клиенте']) ?></small>
    </h1>
</section>
<section class="content body">
    <div class="row">
        <div class="col-md-12">
<?= $this->render('_organization-form', compact('organization', 'buisinessInfo', 'organizationType', 'managersArray')) ?>
        </div>
    </div>
</section>
