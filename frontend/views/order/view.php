<?php

use kartik\grid\GridView;
use kartik\editable\Editable;
use yii\widgets\Pjax;
use common\models\Order;
use common\models\Organization;
use yii\helpers\Html;

$quantityEditable = (in_array($order->status, [
    Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR, 
    Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT,
    Order::STATUS_PROCESSING]));
$priceEditable = ($organizationType == Organization::TYPE_SUPPLIER) && (in_array($order->status, [
    Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR, 
    Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT]));

$js = <<<JS

    socket.on('order$order->id', function (data) {

        var message = JSON.parse(data);

        $( "#notifications" ).prepend( message.body );

    });
        
$('#chat-form').submit(function() {

     var form = $(this);

     $.ajax({
          url: form.attr('action'),
          type: 'post',
          data: form.serialize(),
          success: function (response) {
               $("#message-field").val("");
          }
     });

     return false;
});
JS;
$this->registerJs($js, \yii\web\View::POS_LOAD);

?>
<h3>Заказ <?= ($organizationType == Organization::TYPE_RESTAURANT) ? 'у ' . $order->vendor->name : 'для ' . $order->client->name ?></h3>
<?php Pjax::begin(['enablePushState' => false, 'id' => 'orderContent', 'timeout' => 3000]); ?>
<?=
GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'filterPosition' => false,
    'columns' => [
        'product.product',
        ($quantityEditable) ? 
        [
            'class' => 'kartik\grid\EditableColumn',
            'attribute' => 'quantity',
            'pageSummary' => true,
            'readonly' => false,
            'content' => function($data) {
                return '<div class="text_content">' . htmlentities($data->quantity) . '</div>';
            },
            'editableOptions' => [
                'header' => 'Quantity',
                'inputType' => Editable::INPUT_SPIN,
                'asPopover' => false,
                'options' => [
                    'pluginOptions' => [
                        'initval' => 'quantity',
                        'min' => 0,
                        'step' => 1,
                        'decimals' => 0,
                        'buttonup_class' => 'btn btn-primary',
                        'buttondown_class' => 'btn btn-info',
                        'buttonup_txt' => '<i class="glyphicon glyphicon-plus-sign"></i>',
                        'buttondown_txt' => '<i class="glyphicon glyphicon-minus-sign"></i>'
                    ],
                ],
                'pluginEvents' => [
                    "editableSuccess" => "function(event, val, form, data) { $('#actionButtons').html(data.buttons); }",
                ],
            ],
        ] : 'quantity',
        ($priceEditable) ? 
        [
            'class' => 'kartik\grid\EditableColumn',
            'attribute' => 'price',
            'pageSummary' => true,
            'readonly' => false,
            'content' => function($data) {
                return '<div class="text_content">' . htmlentities($data->price) . '</div>';
            },
            'editableOptions' => [
                'header' => 'Quantity',
                'inputType' => Editable::INPUT_SPIN,
                'asPopover' => false,
                'options' => [
                    'pluginOptions' => [
                        'initval' => 'price',
                        'min' => 0,
                        'max' => PHP_INT_MAX,
                        'step' => 1,
                        'decimals' => 0,
                        'buttonup_class' => 'btn btn-primary',
                        'buttondown_class' => 'btn btn-info',
                        'buttonup_txt' => '<i class="glyphicon glyphicon-plus-sign"></i>',
                        'buttondown_txt' => '<i class="glyphicon glyphicon-minus-sign"></i>'
                    ],
                ],
                'pluginEvents' => [
                    "editableSuccess" => "function(event, val, form, data) { $('#actionButtons').html(data.buttons); }",
                ],
            ],
        ] : 'price',
    //'accepted_quantity',
    ],
]);
?>
<?php Pjax::end(); ?>
<div id="actionButtons">
<?= $this->render('_order-buttons', compact('order', 'organizationType')) ?>    
</div>
<div style="padding-top: 50px;">
    <div class="row">
            <div class="well col-lg-8 col-lg-offset-2">
                <?=
                Html::beginForm(['/order/send-message'], 'POST', [
                    'id' => 'chat-form'
                ])
                ?>

                <div class="row">
                    <div class="col-xs-3">
                        <div class="form-group">
<?= Html::label($user->profile->full_name) ?>
<?= Html::hiddenInput('order_id', $order->id); ?>
                        </div>
                    </div>
                    <div class="col-xs-7">
                        <div class="form-group">
                            <?=
                            Html::textInput('message', null, [
                                'id' => 'message-field',
                                'class' => 'form-control',
                                'placeholder' => 'Message'
                            ])
                            ?>
                        </div>
                    </div>
                    <div class="col-xs-2">
                        <div class="form-group">
<?=
Html::submitButton('Send', [
    'class' => 'btn btn-block btn-success'
])
?>
                        </div>
                    </div>
                </div>

<?= Html::endForm() ?>
                <div id="notifications" ></div>
                <?php
                foreach ($order->orderChat as $chat) {
                    echo "<p><strong>" . $chat->sentBy->profile->full_name . "</strong>: " . $chat->message . "</p>";
                }
                ?>
                </div>
                    
            </div>
        
</div>