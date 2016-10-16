<?php

use kartik\grid\GridView;
use kartik\editable\Editable;
use yii\widgets\Pjax;
use common\models\Order;
use common\models\Organization;
use yii\helpers\Html;
use yii\helpers\Url;

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

    socket.on('user$user->id', function (data) {

        var message = JSON.parse(data);

        messageBody = $.parseHTML( message.body );
       // alert(messageBody);
        
        $( ".direct-chat-messages" ).prepend( message.body );
        
        if (message.isSystem) {
            $.post(
                    "$urlButtons",
                    {"order_id": $order->id}
                ).done(function(result) {
                    $('#actionButtons').html(result);
                });
        }

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
        
$('#actionButtons').on('click', '.btnOrderAction', function() { 
        $.post(
                "$urlOrderAction",
                    {"action": $(this).data("action"), "order_id": $order->id}
                ).done(function(result) {
                    $('#actionButtons').html(result);
                });
    });
JS;
$this->registerJs($js, \yii\web\View::POS_LOAD);
?>
<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title">Заказ <?= ($organizationType == Organization::TYPE_RESTAURANT) ? 'у ' . $order->vendor->name : 'для ' . $order->client->name ?></h3>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <?php Pjax::begin(['enablePushState' => false, 'id' => 'orderContent', 'timeout' => 3000]); ?>
        <?=
        GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'filterPosition' => false,
            'summary' => '',
            //'tableOptions' => ['class' => 'table no-margin'],
            'tableOptions' => ['class'=>'table table-bordered table-striped dataTable'],
            'options' => ['class' => 'table-responsive'],
            'panel' => false,
            'bootstrap' => false,
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
                        'header' => 'Количество',
                        'inputType' => Editable::INPUT_SPIN,
                        'asPopover' => false,
                        'options' => [
                            'pluginOptions' => [
                                'initval' => 'quantity',
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
                        ] : 'quantity',
                ($priceEditable) ?
                        [
                    'class' => 'kartik\grid\EditableColumn',
                    'attribute' => 'price',
                    'pageSummary' => true,
                    'readonly' => false,
                    'content' => function($data) {
                        return '<div class="text_content">' . htmlentities($data->price) . ' <i class="fa fa-fw fa-rub"></i></div>';
                    },
                    'editableOptions' => [
                        'header' => 'Цена',
                        'inputType' => Editable::INPUT_SPIN,
                        'asPopover' => false,
                        'options' => [
                            'pluginOptions' => [
                                'initval' => 'price',
                                'min' => 0,
                                'max' => PHP_INT_MAX,
                                'step' => 0.01,
                                'decimals' => 2,
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
                        ] : [
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
    <div class="box-footer clearfix" id="actionButtons">
        <?= $this->render('_order-buttons', compact('order', 'organizationType')) ?>    
    </div>

</div>

<div class="box box-warning direct-chat direct-chat-warning">
    <div class="box-header with-border">
        <h3 class="box-title">Чат заказа</h3>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <!-- Conversations are loaded here -->
        <div class="direct-chat-messages">
            <?php
            foreach ($order->orderChat as $chat) {
                echo $this->render('_chat-message', [
                    'name' => $chat->sentBy->profile->full_name,
                    'message' => $chat->message,
                    'time' => $chat->created_at,
                    'isSystem' => $chat->is_system,
                    'organizationType' => $chat->sentBy->organization->type_id]);
            }
            ?>
        </div>
        <!--/.direct-chat-messages-->
    </div>
    <!-- /.box-body -->
    <div class="box-footer">
        <?=
        Html::beginForm(['/order/send-message'], 'POST', [
            'id' => 'chat-form'
        ])
        ?>
        <div class="input-group">
            <?= Html::hiddenInput('order_id', $order->id); ?>
            <?= Html::hiddenInput('', $user->profile->full_name, ['id' => 'name']); ?>
            <?=
            Html::textInput('message', null, [
                'id' => 'message-field',
                'class' => 'form-control',
                'placeholder' => 'Сообщение ...'
            ])
            ?>                     
            <span class="input-group-btn">
                <?=
                Html::submitButton('Послать', [
                    'class' => 'btn btn-warning btn-flat'
                ])
                ?>
            </span>
        </div>
        <?= Html::endForm() ?>
    </div>
    <!-- /.box-footer-->
</div>