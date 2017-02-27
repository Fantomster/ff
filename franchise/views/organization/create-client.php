<?php
$organizationType = \common\models\Organization::TYPE_RESTAURANT;
$organization = $client;
?>
<section class="content-header">
    <h1>
        <i class="fa fa-home"></i> Добавить ресторан
        <small>Информация о новом клиенте</small>
    </h1>
</section>
<section class="content body">
    <div class="row">
        <div class="col-md-12">
            <?= $this->render('_organization-form', compact('organization', 'user', 'profile', 'buisinessInfo', 'organizationType')) ?>
        </div>
    </div>
</section>
