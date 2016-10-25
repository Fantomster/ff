<?php

use kartik\grid\GridView;
use yii\widgets\Pjax;
use common\models\Order;
use common\models\Organization;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use kartik\widgets\TouchSpin;
use kartik\form\ActiveForm;

$quantityEditable = (in_array($order->status, [
            Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR,
            Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT,
            Order::STATUS_PROCESSING]));
$priceEditable = ($organizationType == Organization::TYPE_SUPPLIER) && (in_array($order->status, [
            Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR,
            Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT]));

$urlButtons = Url::to(['/order/ajax-refresh-buttons']);
$urlOrderAction = Url::to(['/order/ajax-order-action']);
$js = <<<JS
        $('.content').on('change keyup paste cut', '.quantity', function() {
            dataEdited = 1;
        });
        $(window).on('beforeunload', function(e) {
            if(dataEdited) {
                return 'You have already inputed some text. Sure to leave?';
            }
        });
         
JS;
$this->registerJs($js, \yii\web\View::POS_READY);
?>

<section class="content-header">
    <h1>
        <i class="fa fa-history"></i> Заказ №<?= $order->id ?>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            [
                'label' => 'История заказов',
                'url' => ['order/index'],
            ],
            'Заказ №' . $order->id,
        ],
    ])
    ?>
</section>
<section class="content">
            <?php $form = ActiveForm::begin([
                    'id' => 'generalSettings',
                    'enableAjaxValidation' => false,
                    'options' => [
                        'data-pjax' => true,
                    ],
                    'method' => 'post',
                    'action' => Url::to(['order/edit', 'id' => $order->id]),
        ]);
        ?> 
    <div class="box box-info">
        <div class="box-header">
            <?= Html::submitButton() ?>
        </div>
        <div class="box-body">
            <?php Pjax::begin(['enablePushState' => false, 'id' => 'orderContent', 'timeout' => 3000]); ?>
            <?=
            GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'filterPosition' => false,
                'summary' => '',
                //'tableOptions' => ['class' => 'table no-margin'],
                'tableOptions' => ['class' => 'table table-bordered table-striped dataTable'],
                'options' => ['class' => 'table-responsive'],
                'panel' => false,
                'bootstrap' => false,
                'columns' => [
                    'product.product',
                    [
                        'attribute' => 'quantity',
                        'content' => function($data) {
                            return
                                    TouchSpin::widget([
                                        'name' => "OrderContent[$data->product_id][quantity]",
                                        'id' => "qty$data->product_id",
                                        'pluginOptions' => [
                                            'initval' => $data->quantity,
                                            'min' => 1,
                                            'max' => PHP_INT_MAX,
                                            'step' => 1,
                                            'decimals' => 0,
                                            'buttonup_class' => 'btn btn-default',
                                            'buttondown_class' => 'btn btn-default',
                                            'buttonup_txt' => '<i class="glyphicon glyphicon-plus-sign"></i>',
                                            'buttondown_txt' => '<i class="glyphicon glyphicon-minus-sign"></i>'
                                        ],
                                        'options' => ['class' => 'quantity'],
                            ]) . Html::hiddenInput("OrderContent[$data->product_id][product_id]", $data->product_id);
                        },
                            ],
                            [
                                'format' => 'raw',
                                'attribute' => 'price',
                                'value' => function($data) {
                                    return $data->price . ' <i class="fa fa-fw fa-rub"></i>';
                                },
                                'label' => 'Цена',
                            ],
                        //'accepted_quantity',
                        ],
                    ]);
                    ?>
                    <?php Pjax::end(); ?>
            <!-- /.table-responsive -->
        </div>
        <!-- /.box-body -->
    </div>
            <?php
        ActiveForm::end();
        ?>
</section>