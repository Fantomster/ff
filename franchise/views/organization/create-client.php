<?php
$this->title = Yii::t('app', 'franchise.views.organization.add_rest', ['ru'=>'Добавить ресторан']);
$organizationType = \common\models\Organization::TYPE_RESTAURANT;
$organization = $client;
?>
<section class="content-header">
    <h1>
        <i class="fa fa-home"></i> <?= Yii::t('app', 'franchise.views.organization.add_rest_two', ['ru'=>'Добавить ресторан']) ?>
        <small><?= Yii::t('app', 'franchise.views.organization.new_info', ['ru'=>'Информация о новом клиенте']) ?></small>
    </h1>
</section>
<section class="content body">
    <div class="row">
        <div class="col-md-12">
            <?= $this->render('_organization-form', compact('organization', 'user', 'profile', 'buisinessInfo', 'organizationType', 'managersArray')) ?>
        </div>
    </div>
</section>
