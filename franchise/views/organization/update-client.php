<?php
$organizationType = \common\models\Organization::TYPE_RESTAURANT;
$organization = $client;
?>
<section class="content-header">
    <h1>
        <i class="fa fa-home"></i> <?= Yii::t('app', 'franchise.views.organization.change_rest_info', ['ru'=>'Изменить информацию о ресторане']) ?> <?= $organization->name ?>
        <small><?= Yii::t('app', 'franchise.views.organization.edit_three', ['ru'=>'Редактирование информации о клиенте']) ?></small>
    </h1>
</section>
<section class="content body">
    <div class="row">
        <div class="col-md-12">
            <?= $this->render('_organization-form', compact('organization', 'buisinessInfo', 'organizationType', 'managersArray')) ?>
        </div>
    </div>
</section>
