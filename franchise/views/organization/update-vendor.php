<?php

$organizationType = \common\models\Organization::TYPE_SUPPLIER;
$organization = $vendor;
?>
<section class="content-header">
    <h1>
        <i class="fa fa-home"></i> Изменить информацию о поставщике <?= $organization->name ?>
        <small>Редактирование информации о клиенте</small>
    </h1>
</section>
<section class="content body">
    <div class="row">
        <div class="col-md-12">
<?= $this->render('_organization-form', compact('organization', 'buisinessInfo', 'organizationType', 'managersArray')) ?>
        </div>
    </div>
</section>
