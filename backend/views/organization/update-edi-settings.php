<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\Organization */

$this->title = 'Обновление настройки EDI';
$this->params['breadcrumbs'][] = ['label' => 'Organizations', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="organization-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form-edi-settings', [
        'model'  => $model,
        'providers' => $providers,
        'ediOrganizations' => $ediOrganizations,
        'checkedOrganizations' => $checkedOrganizations,
        'orgID' => $orgID
    ]) ?>

</div>
