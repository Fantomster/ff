<?php

$this->title = Yii::t('app', 'Оператор заказов');
$this->params['breadcrumbs'][] = $this->title;

$wait_second = \common\models\OperatorTimeout::getTimeoutOperator($user_id);

?>
    <style>
        .container {
            width: 1345px;
        }

        .kv-grid-table {
            font-size: 12px;
        }
    </style>

    <div class="order-index">
        <h1><?= \yii\helpers\Html::encode($this->title) ?></h1>

        <?php if ($wait_second > 10): ?>
            <div class="alert alert-danger">Ожидание <span class='timeout_set'><?= $wait_second ?></span> секунд</div>
        <?php endif; ?>

    <div class="summary">
        <h4>Кол-во заказов взятых всеми операторами: </h4>
       <?php
       foreach ($statistic as $row) {
           echo \common\models\OperatorCall::getStatusText($row['status'])." <b>".$row['cnt']."</b></br>";
       }
    echo "</br></div>";
        echo \kartik\grid\GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel'  => $searchModel,
            'options'      => ['style' => 'table-layout:fixed;'],
            'columns'      => [
                [
                    'format'         => 'raw',
                    'header'         => '№',
                    'attribute'      => 'id',
                    'value'          => function ($data) {
                        return \yii\helpers\Html::a(
                            $data['id'],
                            \yii\helpers\Url::to('/order/' . $data['id']),
                            [
                                'target' => '_blank'
                            ]
                        );
                    },
                    'contentOptions' => [
                        'style' => 'width:60px'
                    ]
                ],
                [
                    'attribute'      => 'created_at',
                    'filter'         => \kartik\date\DatePicker::widget([
                        'attribute' => 'created_at',
                        'model'     => $searchModel,
                        'language'  => 'ru',
                        'type'      => 1
                    ]),
                    'value'          => function ($data) {
                        return \Yii::$app->formatter->asDatetime($data['created_at'], 'php:d M Y, H:i:s');
                    },
                    'contentOptions' => [
                        'style' => 'width:100px'
                    ]
                ],
                [
                    'format'         => 'raw',
                    'attribute'      => 'total_price',
                    'value'          => function ($data) {
                        $text = \Yii::$app->formatter->asCurrency($data['total_price'], $data['iso_code']);
                        return \yii\helpers\Html::tag('span', $text, ['style' => 'font-size:12px;']);
                    },
                    'contentOptions' => [
                        'style' => 'width:100px;'
                    ]
                ],
                [
                    'format'         => 'raw',
                    'attribute'      => 'status',
                    'filter'         => \yii\helpers\Html::dropDownList(
                        'OrderOperatorSearch[status]',
                        Yii::$app->request->get('OrderOperatorSearch')['status'] ?? 0,
                        [
                            0                                                        => 'Все',
                            \common\models\Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT => 'Ожидает подтверждения клиента',
                            \common\models\Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR => 'Ожидает подтверждения поставщика',
                            \common\models\Order::STATUS_REJECTED                    => 'Отклонен поставщиком',
                            \common\models\Order::STATUS_CANCELLED                   => 'Отменен клиентом',
                        ],
                        [
                            'class' => 'form-control form-control-xs'
                        ]
                    ),
                    'value'          => function ($data) {
                        $statusList = \common\models\Order::getStatusList();
                        return $statusList[$data['status']];
                    },
                    'contentOptions' => [
                        'style' => 'width:130px'
                    ]
                ],
                [
                    'format'         => 'raw',
                    'header'         => 'Ресторан',
                    'attribute'      => 'client_name',
                    'value'          => function ($data) {
                        return $data['client_title'] . " " . \yii\helpers\Html::a(
                                $data['client_name'],
                                \yii\helpers\Url::to('/organization/' . $data['client_id']),
                                [
                                    'target' => '_blank'
                                ]
                            );
                    },
                    'contentOptions' => [
                        'style' => 'width:150px'
                    ]
                ],
                [
                    'format'         => 'raw',
                    'header'         => 'Поставщик',
                    'attribute'      => 'vendor_name',
                    'value'          => function ($data) {
                        return \yii\helpers\Html::a(
                            $data['vendor_name'],
                            \yii\helpers\Url::to('/organization/' . $data['vendor_id']),
                            [
                                'target' => '_blank'
                            ]
                        );
                    },
                    'contentOptions' => [
                        'style' => 'width:150px'
                    ]
                ],
                /*[
                    'format'         => 'raw',
                    'attribute'      => 'vendor_contact',
                    'value'          => function ($data) {
                        $items = explode(',', $data['vendor_contact']);
                        return implode('<hr style="margin: 2px">', $items);
                    },
                    'contentOptions' => [
                        'style' => 'width:180px;font-size:10px;'
                    ]
                ],*/
                [
                    'format'         => 'raw',
                    'header'         => 'Коментарий к поставщику',
                    'attribute'      => 'vendor_comment',
                    'filter'         => false,
                    'value'          => function ($data) use ($user_id) {
                        $textArea = \yii\helpers\Html::textarea('vendor_comment', $data['vendor_comment'], [
                            'id'       => 'vendor_comment_' . $data['id'],
                            'disabled' => is_null($data['operator']) || $data['status_call_id'] == 3 ? true : false
                        ]);
                        $button = \yii\helpers\Html::button('Сохранить', [
                            'id'             => 'vendor_comment_button_' . $data['id'],
                            'class'          => 'btn btn-success btn-xs save-vendor-comment',
                            'data-vendor_id' => $data['vendor_id'],
                            'data-order_id'  => $data['id'],
                            'disabled'       => is_null($data['operator']) || $data['status_call_id'] == 3 ? true : false
                        ]);
                        return $textArea . '<br>' . $button;
                    },
                    'contentOptions' => [
                        'style' => 'width:100px'
                    ]
                ],
                [
                    'format'         => 'raw',
                    'attribute'      => 'operator',
                    'value'          => function ($data) use ($wait_second) {
                        if (is_null($data['operator'])) {

                            $label = 'Забрать';

                            return \yii\helpers\Html::button($label, [
                                'id'            => 'set_button_' . $data['id'],
                                'class'         => 'btn btn-xs btn-success operator-set-to-order',
                                'data-order_id' => $data['id']
                            ]);
                        } else {
                            return $data['operator_name'];
                        }
                    },
                    'contentOptions' => [
                        'style' => 'width:100px'
                    ]
                ],
                [
                    'format'         => 'raw',
                    'attribute'      => 'status_call_id',
                    'filter'         => \yii\helpers\Html::dropDownList(
                        'OrderOperatorSearch[status_call_id]',
                        Yii::$app->request->get('OrderOperatorSearch')['status_call_id'] ?? 0,
                        array_merge([0 => 'Все'], \common\models\OperatorCall::getStatus()),
                        [
                            'class' => 'form-control form-control-xs'
                        ]
                    ),
                    'value'          => function ($data) use ($user_id) {
                        return
                            \yii\helpers\Html::dropDownList(
                                'OrderOperatorSearch[status_call_id]',
                                $data['status_call_id'] ?? 0,
                                \common\models\OperatorCall::getStatus(),
                                [
                                    'class'         => 'form-control form-control-xs change-status',
                                    'data-order_id' => $data['id'],
                                    'disabled'      => is_null($data['operator']) || $data['status_call_id'] == 3 ? true : false,
                                    'data-current'  => $data['status_call_id']
                                ]
                            );
                    },
                    'contentOptions' => [
                        'style' => 'width:205px'
                    ]
                ],
                [
                    'format'         => 'raw',
                    'header'         => 'Коментарий',
                    'attribute'      => 'comment',
                    'filter'         => false,
                    'value'          => function ($data) use ($user_id) {
                        $textArea = \yii\helpers\Html::textarea('comment', $data['comment'], [
                            'id'       => 'comment_' . $data['id'],
                            'disabled' => is_null($data['operator']) || $data['status_call_id'] == 3 ? true : false
                        ]);
                        $button = \yii\helpers\Html::button('Сохранить', [
                            'id'            => 'comment_button_' . $data['id'],
                            'class'         => 'btn btn-success btn-xs save-comment',
                            'data-order_id' => $data['id'],
                            'disabled'      => is_null($data['operator']) || $data['status_call_id'] == 3 ? true : false
                        ]);
                        return $textArea . '<br>' . $button;
                    },
                    'contentOptions' => [
                        'style' => 'width:100px'
                    ]
                ],
                [
                    'format'         => 'raw',
                    'attribute'      => 'operator_updated_at',
                    'filter'         => \kartik\date\DatePicker::widget([
                        'attribute' => 'operator_updated_at',
                        'model'     => $searchModel,
                        'language'  => 'ru',
                        'type'      => 1
                    ]),
                    'value'          => function ($data) {
                        return \Yii::$app->formatter->asDatetime($data['operator_updated_at'], 'php:d M Y, H:i:s');
                    },
                    'contentOptions' => [
                        'style' => 'width:100px'
                    ]
                ]
            ]
        ]);
        ?>
    </div>

<?php ob_start(); ?>

    var t = setInterval(function () {
    var val = $('.timeout_set').html() - 1;
    if (val <= 0) {
    clearInterval(t);
    $('.timeout_set').parent().remove();
    } else {
    $('.timeout_set').html(val);
    }
    }, 1000);

    $('.change-status').on('change', function (e) {
    var this_ = $(this);
    var id = $(this).data('order_id');
    var value = $(this).val();
    var curr = $(this).data('current');
    $.post('<?= \yii\helpers\Url::to('/order/operator-change-attribute') ?>', {
    id: id,
    name: 'status_call_id',
    value: value
    }, function (data) {
    if(data.length > 0) {
    alert(data);
    if (value == 4) {
    this_.val(curr).change();
    location.reload();
    return true;
    }
    }
    if (value == 3) {
    this_.attr('disabled', 'disabled');
    $('#comment_' + id).attr('disabled', 'disabled');
    $('#comment_button_' + id).attr('disabled', 'disabled');
    $('#set_button_' + id).attr('disabled', 'disabled');
    }
    this_.data('current', value);
    location.reload();
    return true;
    });
    });

    $('.wrap')
    .on('click', '.save-comment', function () {
    var id = $(this).data('order_id');
    var value = $('#comment_' + id).val();
    $.post('<?= \yii\helpers\Url::to('/order/operator-change-attribute') ?>', {
    id: id,
    name: 'comment',
    value: value
    }, function () {
    return true;
    });
    })
    .on('click', '.save-vendor-comment', function () {
    var id = $(this).data('order_id');
    var vendor_id = $(this).data('vendor_id');
    var value = $('#vendor_comment_' + id).val();
    $.post('<?= \yii\helpers\Url::to('/order/operator-change-attribute') ?>', {
    id: vendor_id,
    name: 'vendor_comment',
    value: value
    }, function () {
    location.reload();
    return true;
    });
    })
    .on("click", '.operator-set-to-order', function () {
    var id = $(this).data('order_id');
    $.post('<?= \yii\helpers\Url::to('/order/operator-set-to-order') ?>', {
    id: id
    }, function (data) {
    if (data) {
    alert(data);
    } else {
    location.reload();
    }
    return true;
    });
    });
<?php
$this->registerJs(ob_get_clean(), \yii\web\View::POS_LOAD);
?>