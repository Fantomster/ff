<?php

$this->title = 'Список накладных';
?>

<style>
    .actions_icon {
        margin-right: 5px;
    }
</style>

<section class="content-header">
    <h1>
        <i class="fa fa-upload"></i> <?= $this->title ?>
    </h1>
    <?=
    \yii\widgets\Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            [
                'label' => 'Интеграция',
                'url' => ['/clientintegr/default'],
            ],
            [
                'label' => 'Интеграция Email: ТОРГ - 12',
                'url' => ['/clientintegr/email/default'],
            ],
            $this->title,
        ],
    ])
    ?>
</section>
<section class="content">
    <div class="catalog-index">
        <div class="box box-info">
            <div class="box-header with-border">
                <?= \Yii::$app->controller->module->renderMenu() ?>
            </div>
            <div class="box-body">
                <div class="col-sm-6">
                    <?= \kartik\grid\GridView::widget([
                        'dataProvider' => new \yii\data\ArrayDataProvider(['allModels' => $models]),
                        'summary' => false,
                        'striped' => false,
                        'condensed' => true,
                        'options' => [
                            'style' => 'min-height:300px;max-height:300px;height:300px;overflow-y:scroll;'
                        ],
                        'columns' => [
                            [
                                'attribute' => 'organization_id',
                                'value' => function ($data) {
                                    return $data->organization->name;
                                }
                            ],
                            'number',
                            [
                                'attribute' => 'order_id',
                                'value' => function ($data) {
                                    return $data->order_id ? 'Да' : 'Нет';
                                }
                            ],
                            'created_at',
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'template' => '{view_relations}',
                                'buttons' => [
                                    'view_content' => function ($url, $model) {
                                        return \yii\helpers\Html::tag('span', '', [
                                            'class' => 'actions_icon view-content fa fa-eye',
                                            'data-invoice_id' => $model->id,
                                            'style' => 'cursor:pointer'
                                        ]);
                                    },
                                    'view_relations' => function ($url, $model) {
                                        return \yii\helpers\Html::tag('span', '', [
                                            'class' => 'actions_icon view-relations fa fa-gears',
                                            'data-invoice_id' => $model->id,
                                            'style' => 'cursor:pointer'
                                        ]);
                                    }
                                ],
                            ],
                        ]
                    ])
                    ?>
                </div>
                <div class="col-sm-6"
                     style="min-height:300px;max-height:300px;height:300px;overflow-y:scroll;overflow-x:auto;">
                    <div id="invoice-content"></div>
                </div>
                <div class="col-sm-12">
                    <div id="invoice-orders"></div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
ob_start();
?>

    $('.view-relations').click(function () {

        var $invoice_id = $(this).data('invoice_id');

        swal({
                input: 'select',
                confirmButtonText: 'Выбрать',
                showCancelButton: false,
                title: 'Выберите поставщика',
                inputOptions: new Promise(function (resolve) {
                    $.post('/ru/clientintegr/email/invoice/get-suppliers', function (data) {
                        resolve(data);
                    });
                })
            }
        ).then(function (result) {
            if (result.value) {
                $.get('invoice/get-orders', {
                    OrderSearch: {vendor_search_id: result.value, vendor_id: result.value},
                    invoice_id: $invoice_id
                }, function (data) {
                    $('#invoice-orders').html(data);
                });

                $.post('/ru/clientintegr/email/invoice/get-content', {
                    id: $invoice_id,
                    vendor_id: result.value
                }, function (data) {
                    $('#invoice-content').html(data);
                });
            }
        });
    });

    $('.box-body').on('click', '.create-order', function () {
        $.post('invoice/create-order', $(this).data(), function (data) {
            if (data.status === true) {
                swal({title: 'Готово!', type:'success'});
            } else {
                swal({title: 'Ошибка!', text: data.error, type:'error'});
            }
        });
    });
<?php
$this->registerJs(ob_get_clean());
?>
