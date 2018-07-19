<?php

use kartik\grid\GridView;
use kartik\widgets\Select2;

$this->title = 'Список накладных';


function renderButton($id)
{
    return \yii\helpers\Html::tag('a', 'Задать', [
        'class' => 'actions_icon view-relations',
        'data-toggle'=>"modal",
        'data-target'=>"#myModal",
        'data-invoice_id' => $id,
        'style' => 'cursor:pointer;align:center;color:red;',
        'href' => '#'
    ]);
}

?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
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
                            // 'dataProvider' => new \yii\data\ArrayDataProvider(['allModels' => $models]),
                            'dataProvider' => $dataProvider,
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
                                    'attribute' => 'number',
                                    'filterInputOptions' => [
                                        'class'       => 'form-control',
                                        'placeholder' => '№ накладной'
                                    ],
                                    'value' => function ($data) {
                                        if ($data->order_id) return '';
                                        return \yii\helpers\Html::input('radio', 'invoice_id', $data->id, ['class' => 'invoice_radio']);
                                    },
                                    'contentOptions' => ['class' => 'text-center'],
                                    'headerOptions' => ['style' => 'width: 100px;'],
                                ],
                                [
                                        'format'=>'raw',
                                        'header'=>'Номер накладной',
                                        'attribute' => 'name_postav',
                                        'filterInputOptions' => [
                                            'class'       => 'form-control',
                                            'placeholder' => 'Наименование поставщика'
                                        ],
                                        'value'=>function($data){

                                            $user = Yii::$app->user->identity;
                                            $licenses = $user->organization->getLicenseList();
                                             $timestamp_now=time();
                                                if(isset($licenses['rkws'])) {
                                                    $sub0 = explode(' ',$licenses['rkws']->td);
                                                    $sub1 = explode('-',$sub0[0]);
                                                    $licenses['rkws']->td = $sub1[2].'.'.$sub1[1].'.'.$sub1[0];
                                                    if ($licenses['rkws']->status_id==0) $rk_us=0;
                                                    if (($licenses['rkws']->status_id==1) and ($timestamp_now<=(strtotime($licenses['rkws']->td)))) $link='rkws';

                                                    /*$sub0 = explode(' ',$licenses['rkws_ucs']->td);
                                                    $sub1 = explode('-',$sub0[0]);
                                                    $licenses['rkws_ucs']->td = $sub1[2].'.'.$sub1[1].'.'.$sub1[0];
                                                    if ($licenses['rkws_ucs']->status_id==0) $rk_lic=0;
                                                    if (($licenses['rkws_ucs']->status_id==1) and ($timestamp_now<=(strtotime($licenses['rkws_ucs']->td)))) $rk_lic=3;
                                                    if (($licenses['rkws_ucs']->status_id==1) and (($timestamp_now+14*86400)>(strtotime($licenses['rkws_ucs']->td)))) $rk_lic=2;
                                                    if (($licenses['rkws_ucs']->status_id==1) and ($timestamp_now>(strtotime($licenses['rkws_ucs']->td)))) $rk_lic=1;*/
                                                }
                                                if(isset($licenses['iiko'])) {
                                                    $sub0 = explode(' ',$licenses['iiko']->td);
                                                    $sub1 = explode('-',$sub0[0]);
                                                    $licenses['iiko']->td = $sub1[2].'.'.$sub1[1].'.'.$sub1[0];
                                                    if ($licenses['iiko']->status_id==0) $lic_iiko=0;
                                                    if (($licenses['iiko']->status_id==1) and ($timestamp_now<=(strtotime($licenses['iiko']->td)))) $link='iiko';
                                                }
                                                if(!isset($link))
                                                {
                                                    return $data->number;
                                                }else{
                                                    return (!empty($data->order_id))?\yii\helpers\Html::a($data->number,['/clientintegr/'.$link.'/waybill/index','way'=>$data->order_id]):$data->number;
                                                }


                                        }
                                ],
                                [
                                    'attribute' => 'date',
                                    'format'=>'raw',
                                    'filterInputOptions' => [
                                        'class'       => 'form-control',
                                        'placeholder' => 'Дата'
                                    ],
                                    'value' => function($row){
                                        return \Yii::$app->formatter->asDatetime(new DateTime($row->date), 'php:Y-m-d');
                                    }
                                ],
                                [
                                    'format'=>'raw',
                                    'header'=>'Наименование поставщика',
                                    'value'=>function($data){
                                        return $data->name_postav;
                                    }
                                ],
                                [
                                    'attribute' => 'organization_id',
                                    'value' => function ($data) {
                                        //return $data->organization->name;
                                        if ($data->consignee) return $data->consignee;
                                        else return $data->organization->name;
                                    }
                                ],
                                [
                                    'attribute' => 'count',
                                    'value' => function ($data) {
                                        return count($data->content);
                                    }
                                ],
                                [
                                    'format' => 'raw',
                                    'attribute' => 'created_at',
                                    'value' => function($data) {
                                        return Yii::$app->formatter->asDatetime($data->created_at, "php:Y-m-d H:i:s");
                                    },
                                ],
                                [
                                    'attribute' => 'order_id',
                                    'value' => function ($data) {
                                        return $data->order_id ? $data->order_id : 'Нет';
                                    }
                                ],
                                [
                                    'attribute' => 'total_sum_withtax',
                                    'value' => function ($data) {
                                        return number_format($data->total_sum_withtax, 2, '.', ' ');
                                    }
                                ],
                                [
                                    'header' => 'Связь с поставщиком',
                                    'class' => 'yii\grid\ActionColumn',
                                    'template' => '{view_relations}',
                                    'contentOptions' => ['style' => 'text-align:center'],
                                    'buttons' => [
                                        /*'view_content' => function ($url, $model) {
                                            return \yii\helpers\Html::tag('span', '', [
                                                'class' => 'actions_icon view-content fa fa-eye',
                                                'data-invoice_id' => $model->id,
                                                'style' => 'cursor:pointer'
                                            ]);
                                        },*/
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

$user = \Yii::$app->user->identity;
/**
 * @var $organization \common\models\Organization
 */
$organization = $user->organization;
$integration = $organization->integrationOnly();

$list_integration = '';

if(!empty($integration)) {
    $links = [
            'rk' => [
                'title' => 'R_keeper',
                'url' => '/clientintegr/rkws/waybill/index'
            ],
            'iiko' => [
                'title' => 'iiko Office',
                'url' => '/clientintegr/iiko/waybill/index'
            ]
    ];
    foreach($integration as $key => $row) {
        $list_integration .=  '<br>' . \yii\helpers\Html::a($links[$key]['title'], \Yii::$app->urlManager->createUrl($links[$key]['url']),[
            'class' => 'btn btn-primary'
        ]);
    }
}


//echo ;

ob_start();


?>


    $('.view-relations').click(function () {
        var $invoice_id = $(this).data('invoice_id');
        var td = $(this).parents('tr').find('td:last-child');
        var this_ = $(this);
        var organization_id = <?=$organization->id?>;



        /*swal({
            title: "Выбрать поставщика",
            html: "<select style='width:100%;' class='search_post'></select>",
            confirmButtonColor: '#26C281',
            confirmButtonText: 'Выбрать',
            confirmButtonColor: '#26C281',
            showCancelButton: true,
            cancelButtonText: 'Отменить',
            cancelButtonColor: '#EF4836',
            focusConfirm: false,
            preConfirm: function () {
                    if ($('.search_post').select2('val') != null){
                        console.log($('.search_post').select2('data')[0]);
                    }
            },
            onOpen: function () {
               var data = $('.search_post').select2({
                    placeholder: "Выбрать поставщика",
                    dropdownParent: $('.swal2-container'),
                    allowClear: true,
                    language: "ru",
                    ajax: {
                        url: '<?= $url ?>/get-suppliers',
                        dataType: 'json',
                        processResults: function (data) {
                            return {
                                results: data
                            };
                        }
                    },
                });
            },
        }).then(function(result){
            console.log(result);
        });*/

        swal({
                html: '<input type="text" id="bukv-postav" class="swal2-input" placeholder="Введите хотя бы две буквы названия поставщика" autofocus>'+'<div id="bukv-postav2"></div>',
                inputPlaceholder: 'Введите хотя бы две буквы',
                confirmButtonText: 'Выбрать',
                cancelButtonText: 'Отмена',
                showCancelButton: true,
                title: 'Выберите поставщика',
                inputOptions: new Promise(function (resolve) {
                    $(document).ready ( function(){
                        $("#bukv-postav").focus();
                        $("#bukv-postav").keyup(function() {
                            var a = $("#bukv-postav").val();
                                if (a.length>=2) {
                                    $.post('<?=$url?>/list-postav', {org_id: organization_id, stroka: a}).done(
                                    function(data){
                                    var arr = JSON.parse(data);
                                    if (arr.length>0) {
                                        var sel = '<select id="selpos" name="list_postav" class="swal2-input">';
                                        var index;
                                        for (index = 0; index < arr.length; ++index) {
                                            sel = sel+'<option value="'+arr[index]['id']+'">'+arr[index]['name']+'</option>';
                                        }
                                        sel = sel+'</select>';
                                    } else {
                                        sel = 'Нет данных.';
                                    }
                                    $('#bukv-postav2').html(sel);
                                });
                            } else {
                                $('#bukv-postav2').text('');
                            }
                        });
                    });
                })
            }
        ).then(function (result) {
            if(result.value) {
                var selectd = $("#selpos").val();
                if (selectd) {
                    var selected_name = $("#selpos option:selected").text();
                    console.log(selected_name);
                    if (selectd!=-1) {
                        $.get('<?= $url ?>/get-orders', {
                            OrderSearch: {vendor_search_id: selectd, vendor_id: selectd},
                            invoice_id: $invoice_id
                        }, function (data) {
                            $('#invoice-orders').html(data);
                            $('.orders').show();
                            $(this_).data('vendor_id', selectd);
                            $(this_).html(selected_name);
                        });
                    }
                }
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

        var button = $(this);

        if(button.attr('disabled') === 'disabled') {
            alert('Идет обработка накладной, подождите...');
            return;
        }

        button.attr('disabled', 'disabled');
        button.html('Сохранение...');

        var params = {};
        var create_order = true;
        row_invoice = $('.invoice_radio:checked').parents('tr');
        params.invoice_id = $(row_invoice).find('.invoice_radio:checked').val();
        params.vendor_id = $(row_invoice).find('.view-relations').data('vendor_id');
        params.order_id = $('.orders_radio:checked').val();

        if (params.invoice_id === undefined) {
            errorSwal('Необходимо выбрать накладную.');
            button.removeAttr('disabled', false);
            button.html('<i class="fa fa-save"></i> Сохранить');
            return false;
        }

        if (params.vendor_id === undefined) {
            errorSwal('Необходимо задать связь с поставщиком.');
            button.removeAttr('disabled', false);
            button.html('<i class="fa fa-save"></i> Сохранить');
            return false;
        }

        if (params.order_id === undefined) {
            create_order = false;
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
                $.post('<?= $url ?>/create-order', params, function (data) {
                    if (data.status === true) {
                        $('input[class="orders_radio"]').prop('checked', false);
                        $('.catalog-index.orders').hide();
                        $(row_invoice).find('.invoice_radio').remove();
                        swal(
                            'Накладная успешно привязана!',
                            'Перейти в интеграцию: <?= $list_integration ?>',
                            'success'
                        );
                    } else {
                        errorSwal(data.error)
                    }
                    button.removeAttr('disabled', false);
                    button.html('<i class="fa fa-save"></i> Сохранить');
                    });
                    button.removeAttr('disabled', false);
                    button.html('<i class="fa fa-save"></i> Сохранить');
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
$this->registerJsFile(
    'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js',
    ['depends' => [\yii\web\JqueryAsset::className()]]
);
?>
