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

    <?=
    DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'client_id',
            'vendor_id',
            'created_by_id',
            'accepted_by_id',
            'status',
            'total_price',
            [
                'attribute' => 'created_at',
                'label' => 'Дата заказа',
                'value' => function ($data) {
                    return Yii::$app->formatter->asTime($data->created_at, "php:j M Y, H:i:s");
                }
            ],
            [
                'attribute' => 'updated_at',
                'label' => 'Последнее изменение',
                'value' => function ($data) {
                    return Yii::$app->formatter->asTime($data->updated_at, "php:j M Y, H:i:s");
                }
            ],
            [
                'attribute' => 'requested_delivery',
                'label' => 'Запрошенная дата доставки',
                'value' => function ($data) {
                    return Yii::$app->formatter->asTime($data->requested_delivery, "php:j M Y, H:i:s");
                }
            ],
            [
                'attribute' => 'actual_delivery',
                'label' => 'Фактическая дата доставки',
                'value' => function ($data) {
                    return Yii::$app->formatter->asTime($data->actual_delivery, "php:j M Y, H:i:s");
                }
            ],
            'comment:ntext',
            'discount',
            'discount_type',
        ],
    ])
    ?>

</div>
