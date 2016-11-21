<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\models\Organization;

/* @var $this yii\web\View */
/* @var $model common\models\Organization */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Organizations', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

switch ($model->type_id) {
    case Organization::TYPE_RESTAURANT:
        $orderListUrl = ['order/index', 'OrderSearch[client_id]' => $model->id];
        break;
    case Organization::TYPE_SUPPLIER:
        $orderListUrl = ['order/index', 'OrderSearch[vendor_id]' => $model->id];
        break;
}
?>
<div class="organization-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'label' => 'Тип организации',
                'value' => $model->type->name,
            ],
            'name',
            'city',
            'address',
            'zip_code',
            'phone',
            'email:email',
            'website',
            'created_at',
            'updated_at',
            'step',
            [
                'format' => 'raw',
                'label' => 'Работники',
                'value' => Html::a('Список', ['client/index', 'UserSearch[organization_id]'=>$model->id])
            ],
            [
                'format' => 'raw',
                'label' => 'Заказы',
                'value' => Html::a('Список', $orderListUrl),
            ],
        ],
    ]) ?>

</div>
