<?php

use kartik\grid\GridView;
use kartik\editable\Editable;
use yii\widgets\Pjax;
use common\models\Order;
use common\models\Organization;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

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
    <div class="row">
        <div class="col-md-8" id="toPrint">
            <div class="box box-info">
                <div class="box-header with-border">
                    <div class="row m-b-xl">
                        <div class="col-xs-6">
                            <h4 class="font-bold">Заказ №<?= $order->id ?></h4>
                            <span>Заказчик:</span>
                            <address>
                                <strong><?= $order->client->name ?></strong><br>
                                <?= $order->client->city ?><br>
                                адрес: <?= $order->client->address ?><br>
                                телефон: <?= $order->client->phone ?>
                            </address>
                            <p class="text-left">
                                <span>Размещен:</span>
                                <strong><?= $order->createdBy->profile->full_name ?></strong><br>
                                email: <?= $order->createdBy->email ?>
                            </p>
                            <p class="text-left">
                                <strong>Запрошенная дата доставки:</strong><br>
                                <?= $order->requested_delivery ?>
                            </p>
                        </div>
                        <div class="col-xs-6 text-right">
                            <h4 class="font-bold">&nbsp;</h4>
                            <span>Поставщик:</span>
                            <address>
                                <strong><?= $order->vendor->name ?></strong><br>
                                <?= $order->vendor->city ?><br>
                                адрес: <?= $order->vendor->address ?><br>
                                телефон: <?= $order->vendor->phone ?>
                            </address>
                            <p class="text-right">
                                <span><strong>Дата создания заказа:</strong><br><?= $order->created_at ?></span><br>
                                <span><strong>Дата доставки:</strong><br><?= $order->actual_delivery ?></span>
                            </p>
                        </div>
                    </div>
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
                        'tableOptions' => ['class' => 'table table-bordered table-striped dataTable'],
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
                                            'buttonup_class' => 'btn btn-default',
                                            'buttondown_class' => 'btn btn-default',
                                            'buttonup_txt' => '<i class="glyphicon glyphicon-plus-sign"></i>',
                                            'buttondown_txt' => '<i class="glyphicon glyphicon-minus-sign"></i>'
                                        ],
                                    ],
                                    'submitButton' => [
                                        'class' => 'btn btn-sm btn-success kv-editable-submit',
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

        </div>
        <div class="col-md-4">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Итого</h3>
                </div>
                <div class="box-body">
                    <p class="text-left m-b-sm"><b>Дата создания заказа:</b><br>
                        <?= $order->created_at ?></p>
                    <p class="text-left m-b-sm"><b>Стоимость доставки:</b><br>
                        <?= $order->vendor->delivery->delivery_charge ?></p>
                    <p class="text-left m-b-sm"><b>Стоимость заказа:</b><br>
                        <?= $order->total_price ?></p>
                    <div id="actionButtons">
                    <?= $this->render('_order-buttons', compact('order', 'organizationType')) ?>   
                    </div>
                </div>
            </div>
            <?php
            echo Html::beginForm(Url::to(['/order/ajax-refresh-buttons']), 'post', ['id' => 'actionButtonsForm']);
            echo Html::hiddenInput('order_id', $order->id);
            echo Html::endForm();
            ?>

        </div>
        <div class="col-md-4">
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
                                'id' => $chat->id,
                                'name' => $chat->sentBy->profile->full_name,
                                'sender_id' => $chat->sent_by_id,
                                'message' => $chat->message,
                                'time' => $chat->created_at,
                                'isSystem' => $chat->is_system,
                                'ajax' => 0,
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
                        <?= Html::hiddenInput('sender_id', $user->id, ['id' => 'sender_id']); ?>
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
        </div>
    </div>
</section>