<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Order */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Orders', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="order-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'client_id',
            'vendor_id',
            'created_by_id',
            'accepted_by_id',
            'status',
            'total_price',
            'created_at',
            'updated_at',
            'requested_delivery',
            'actual_delivery',
            'comment:ntext',
            'discount',
            'discount_type',
        ],
    ]) ?>

</div>
