<?php

use kartik\grid\GridView;

$this->title = 'Список накладных';


function renderButton($id)
{
    return \yii\helpers\Html::tag('a', 'Задать', [
        'class' => 'actions_icon view-relations',
        'data-invoice_id' => $id,
        'style' => 'cursor:pointer;align:center;color:red;',
        'href' => '#'
    ]);
}

?>

<style>
    .actions_icon {
        margin-right: 5px;
        text-decoration: underline;
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
                <a href="#"
                   class="btn btn-success pull-right" id="save-button">
                    <i class="fa fa-save"></i> Сохранить
                </a>
            </div>
            <div class="box-body">
                <div class="col-sm-12">
                    <?php try {
                        echo GridView::widget([
                            'dataProvider' => new \yii\data\ArrayDataProvider(['allModels' => $models]),
                            'summary' => false,
                            'striped' => false,
                            'condensed' => true,
                            'hover' => false,
                            'options' => [
                                'style' => 'min-height:300px;max-height:300px;height:300px;overflow-y:scroll;'
                            ],
                            'columns' => [
                                [
                                    'header' =>
                                        'выбрать / ' . \yii\helpers\Html::tag('i', '', ['class' => 'fa fa-close clear_invoice_radio', 'style' => 'cursor:pointer;color:red']),
                                    'format' => 'raw',
                                    'value' => function ($data) {
                                        if($data->order_id) return '';
                                        return \yii\helpers\Html::input('radio', 'invoice_id', $data->id, ['class' => 'invoice_radio']);
                                    },
                                    'contentOptions' => ['class' => 'text-center'],
                                    'headerOptions' => ['style' => 'width: 100px;'],
                                ],
                                'number',
                                [
                                    'attribute' => 'organization_id',
                                    'value' => function ($data) {
                                        return $data->organization->name;
                                    }
                                ],
                                [
                                    'attribute' => 'count',
                                    'value' => function ($data) {
                                        return count($data->content);
                                    }
                                ],
                                'created_at',
                                [
                                    'attribute' => 'order_id',
                                    'value' => function ($data) {
                                        return $data->order_id ? 'Да' : 'Нет';
                                    }
                                ],
                                [
                                    'attribute' => 'total',
                                    'value' => function ($data) {
                                        return number_format($data->totalSumm, 2, '.', ' ');
                                    }
                                ],
                                [
                                    'header' => 'Связь с поставщиком',
                                    'class' => 'yii\grid\ActionColumn',
                                    'template' => '{view_relations}',
                                    'contentOptions' => ['style' => 'text-align:center'],
                                    'buttons' => [
                                        'view_content' => function ($url, $model) {
                                            return \yii\helpers\Html::tag('span', '', [
                                                'class' => 'actions_icon view-content fa fa-eye',
                                                'data-invoice_id' => $model->id,
                                                'style' => 'cursor:pointer'
                                            ]);
                                        },
                                        'view_relations' => function ($url, $model) {
                                            if (isset($model->order->vendor)) {
                                                return $model->order->vendor->name;
                                            } else {
                                                return renderButton($model->id);
                                            }
                                        }
                                    ],
                                ],
                                [
                                    'class' => 'kartik\grid\ExpandRowColumn',
                                    'width' => '50px',
                                    'value' => function ($model, $key, $index, $column) {
                                        return GridView::ROW_COLLAPSED;
                                    },
                                    'detail' => function ($model, $key, $index, $column) {
                                        return \Yii::$app->controller->renderPartial('_content', ['model' => $model]);
                                    },
                                    'expandOneOnly' => true,
                                    'enableRowClick' => false,
                                    'allowBatchToggle' => false
                                ],
                            ]
                        ]);
                    } catch (Exception $e) {
                    } ?>
                </div>
            </div>
        </div>
    </div>
    <div class="catalog-index orders" style="display:none">
        <div class="box box-info">
            <div class="box-body">
                <div class="col-sm-12">
                    <div id="invoice-orders"></div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
$url = \Yii::$app->urlManager->createUrl('/clientintegr/email/invoice');
ob_start();
?>
    $('.view-relations').click(function () {

        var vendors = {};
        var $invoice_id = $(this).data('invoice_id');
        var td = $(this).parents('tr').find('td:last-child');
        var this_ = $(this);

        swal({
                input: 'select',
                confirmButtonText: 'Выбрать',
                cancelButtonText: 'Отмена',
                showCancelButton: true,
                title: 'Выберите поставщика',
                inputOptions: new Promise(function (resolve) {
                    $.post('<?= $url ?>/get-suppliers', function (data) {
                        vendors = data;
                        resolve(vendors);
                    });
                })
            }
        ).then(function (result) {
            if (result.value) {
                $.get('<?= $url ?>/get-orders', {
                    OrderSearch: {vendor_search_id: result.value, vendor_id: result.value},
                    invoice_id: $invoice_id
                }, function (data) {
                    $('#invoice-orders').html(data);
                    $('.orders').show();
                    $(this_).data('vendor_id', result.value);
                    $(this_).html(vendors[result.value]);
                });
            }
        });
    });

    $('.box-body').on('click', '.clear_radio', function () {
        $('.orders_radio').prop('checked', false);
    });

    $('.box-body').on('click', '.clear_invoice_radio', function () {
        $('.invoice_radio').prop('checked', false);
    });

    $('#save-button').click(function () {
        row_invoice = $('.invoice_radio:checked').parents('tr');
        var params = {};
        params.invoice_id = $(row_invoice).find('.invoice_radio:checked').val();
        params.vendor_id = $(row_invoice).find('.view-relations').data('vendor_id');
        params.order_id = $('.orders_radio:checked').val();

        if (params.invoice_id === undefined) {
            errorSwal('Необходимо выбрать накладную.');
            return false;
        }

        if (params.vendor_id === undefined) {
            errorSwal('Необходимо задать связь с поставщиком.');
            return false;
        }

        if (params.order_id === undefined) {
            swal({
                title: 'Будет создан новый заказ?',
                text: "Вы уверены что не хотите прикрепить накладную к существующему заказу.",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Продолжить',
                cancelButtonText: 'Отмена',
            }).then(function (result) {
                if (result.value) {
                    $.post('<?= $url ?>/create-order', params, function (data) {
                        if (data.status === true) {
                            swal(
                                'Накладная успешно привязана!',
                                'Перейти в интеграцию: ',
                                'success'
                            );
                        } else {
                            errorSwal(data.error)
                        }
                    });
                }
            });
        }
    });

    function errorSwal(message) {
        swal(
            'Ошибка',
            message,
            'error'
        )
    }
<?php
$this->registerJs(ob_get_clean());
?>
