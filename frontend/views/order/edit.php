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
//        $('.content').on('change keyup paste cut', '.quantity', function() {
//            dataEdited = 1;
//            alert(1);
//        });
        $('.content').on('change keyup paste cut', '.price', function() {
            dataEdited = 1;
            alert(2);
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
    <?php
    $form = ActiveForm::begin([
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
            <div id="orderGrid">
                <?= $this->render('_edit-grid', compact('dataProvider', 'searchModel')) ?>
            </div>
                            <?php Pjax::end(); ?>
                            <!-- /.table-responsive -->
                        </div>
                        <!-- /.box-body -->
                    </div>
                    <?php
                    ActiveForm::end();
                    ?>
</section>