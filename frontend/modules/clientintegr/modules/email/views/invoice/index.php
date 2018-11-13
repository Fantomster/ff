<?php

use kartik\grid\GridView;
use kartik\widgets\Select2;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
use yii\helpers\Url;

/* function renderButton($id)
  {
  return \yii\helpers\Html::tag('a', 'Задать', [
  'class' => 'actions_icon view-relations',
  'data-toggle' => "modal",
  'data-target' => "#myModal",
  'data-invoice_id' => $id,
  'style' => 'cursor:pointer;align:center;color:red;',
  'href' => '#'
  ]);
  } */

Pjax::begin(['enablePushState' => false, 'id' => 'order-list',]);
$form = ActiveForm::begin([
    'options'                => [
        'data-pjax' => false,
        'id'        => 'search-form',
        'role'      => 'search',
    ],
    'enableClientValidation' => false,
    'method'                 => 'get',
]);

$this->title = 'Список накладных';
$this->registerJs('
    $("document").ready(function(){
        var justSubmitted = false;
        $(".box-body").on("change", "#number", function() {
            $("#search-form").submit();
        });
        //$(".box-body").on("change", "#orgFilter", function() {
        //    $("#search-form").submit();
        //});
        $(".box-body").on("change", "#dateFrom, #dateTo", function() {
            
            if (!justSubmitted) {console.log(\'время\');
                $("#search-form").submit();
                justSubmitted = true;
                setTimeout(function() {
                    justSubmitted = false;
                }, 500);
            }
        });
        $(".box-body").on("change keyup paste cut", "#name_postav_filter", function() {
            if (timer) {
                clearTimeout(timer);
            }
            timer = setTimeout(function() {
                $("#search-form").submit();
            }, 700);
            setTimeout(function() {
                $(\'.editCatalogButtons\').removeAttr(\'disabled\');
            }, 2000);
});
    });
');
?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet"/>
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
        'links'   => [
            [
                'label' => 'Интеграция',
                'url'   => ['/clientintegr/default'],
            ],
            [
                'label' => 'Интеграция Email: ТОРГ - 12',
                'url'   => ['/clientintegr/email/default'],
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
                <span id="button_save">
                        <a href="#"
                           class="btn btn-success pull-right" id="save-button" disabled>
                            <i class="fa fa-save"></i> Сохранить
                        </a>
                    </span>
            </div>

            <div class="box-body">

                <div class="row">
                    <div class="col-lg-2 col-md-3 col-sm-6">
                        <?php
                        echo $form->field($searchModel, 'number')
                            ->textInput(['prompt' => 'Поиск', 'class' => 'form-control', 'id' => 'number'])
                            ->label(Yii::t('message', 'frontend.views.torg12.number', ['ru' => 'Номер накладной']), ['class' => 'label', 'style' => 'color:#555']);
                        ?>
                    </div>
                    <div class="col-lg-3 col-md-5 col-sm-9">
                        <?= Html::label(Yii::t('message', 'frontend.views.order.begin_end', ['ru' => 'Дата: Начальная дата / Конечная дата']), null, ['class' => 'label', 'style' => 'color:#555']) ?>
                        <div class="form-group" style="width: 300px; height: 44px;">
                            <?=
                            DatePicker::widget([
                                'model'         => $searchModel,
                                'attribute'     => 'date_from',
                                'attribute2'    => 'date_to',
                                'options'       => ['placeholder' => Yii::t('message', 'frontend.views.order.date', ['ru' => 'Дата']), 'id' => 'dateFrom'],
                                'options2'      => ['placeholder' => Yii::t('message', 'frontend.views.order.date_to', ['ru' => 'Конечная дата']), 'id' => 'dateTo'],
                                'separator'     => '-',
                                'type'          => DatePicker::TYPE_RANGE,
                                'pluginOptions' => [
                                    'format'    => 'dd.mm.yyyy', //'d M yyyy',//
                                    'autoclose' => true,
                                    'endDate'   => "0d",
                                ]
                            ])
                            ?>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-3 col-sm-6">
                        <?php
                        echo $form->field($searchModel, 'name_postav')
                            ->textInput(['prompt' => 'Поиск', 'class' => 'form-control fa fa-search', 'id' => 'name_postav_filter'])
                            ->label(Yii::t('message', 'frontend.views.supplier.denome', ['ru' => 'Наименование поставщика']), ['class' => 'label', 'style' => 'color:#555']);
                        ?>
                    </div>
                </div>
                <?php ActiveForm::end(); ?>
                <?php ?>
                <div class="col-sm-12">
                    <?php
                    try {
                        $dataProvider->pagination->pageParam = 'page_outer';
                        echo GridView::widget([
                            'dataProvider' => $dataProvider,
                            'summary'      => false,
                            'striped'      => false,
                            'condensed'    => true,
                            'hover'        => false,
                            'options'      => [
                                'style' => 'min-height:300px;max-height:300px;height:300px;overflow-y:scroll;'
                            ],
                            'columns'      => [
                                [
                                    'header'             =>
                                        'выбрать / ' . \yii\helpers\Html::tag('i', '', ['class' => 'fa fa-close clear_invoice_radio', 'style' => 'cursor:pointer;color:red']),
                                    'format'             => 'raw',
                                    'attribute'          => 'number',
                                    'filterInputOptions' => [
                                        'class'       => 'form-control',
                                        'placeholder' => '№ накладной'
                                    ],
                                    'value'              => function ($model) {
                                        if ($model->order_id) {
                                            return ' ';
                                        }
                                        if (!($model->vendor_id)) {
                                            return '';
                                        }
                                        //return \yii\helpers\Html::input('radio', 'invoice_id', $model->id, ['class' => 'invoice_radio']);
                                        //return '<button type="button" class="btn-primary invoice_radio" id="'.$model->id.'" title="Применить"><i class="glyphicon glyphicon-ok"></i></button>';
                                        return '<a href="#" class="btn btn-secondary btn-lg invoice_radio" role="button" aria-disabled="true" id="' . $model->id . '">☐</a>';
                                    },
                                    //'contentOptions' => ['class' => 'text-center'],
                                    'contentOptions'     => function ($model) {
                                        return ["id" => "rbutton" . $model->id];
                                    },
                                    'headerOptions'      => ['style' => 'width: 100px;'],
                                ],
                                [
                                    'format'             => 'raw',
                                    'header'             => 'Номер накладной',
                                    'attribute'          => 'invoice_id',
                                    'filterInputOptions' => [
                                        'class'       => 'form-control',
                                        'placeholder' => 'Наименование поставщика'
                                    ],
                                    'contentOptions'     => function ($model) {
                                        return ["id" => "nn" . $model->id];
                                    },
                                    'value'              => function ($data) {

                                        $user = Yii::$app->user->identity;
                                        $licenses = $user->organization->getLicenseList();
                                        $timestamp_now = time();
                                        if (isset($licenses['rkws'])) {
                                            $sub0 = explode(' ', $licenses['rkws']->td);
                                            $sub1 = explode('-', $sub0[0]);
                                            $licenses['rkws']->td = $sub1[2] . '.' . $sub1[1] . '.' . $sub1[0];
                                            if ($licenses['rkws']->status_id == 0) {
                                                $rk_us = 0;
                                            }
                                            if (($licenses['rkws']->status_id == 1) and ($timestamp_now <= (strtotime($licenses['rkws']->td)))) {
                                                $link = 'rkws';
                                            }
                                        }
                                        if (isset($licenses['iiko'])) {
                                            $sub0 = explode(' ', $licenses['iiko']->td);
                                            $sub1 = explode('-', $sub0[0]);
                                            $licenses['iiko']->td = $sub1[2] . '.' . $sub1[1] . '.' . $sub1[0];
                                            if ($licenses['iiko']->status_id == 0) {
                                                $lic_iiko = 0;
                                            }
                                            if (($licenses['iiko']->status_id == 1) and ($timestamp_now <= (strtotime($licenses['iiko']->td)))) {
                                                $link = 'iiko';
                                            }
                                        }
                                        if (isset($licenses['tillypad'])) {
                                            $sub0 = explode(' ', $licenses['tillypad']->td);
                                            $sub1 = explode('-', $sub0[0]);
                                            $licenses['tillypad']->td = $sub1[2] . '.' . $sub1[1] . '.' . $sub1[0];
                                            if ($licenses['tillypad']->status_id == 0) {
                                                $lic_tilly = 0;
                                            }
                                            if (($licenses['tillypad']->status_id == 1) and ($timestamp_now <= (strtotime($licenses['tillypad']->td)))) {
                                                $link = 'tillypad';
                                            }
                                        }
                                        if (!$data->order_id) {
                                            return $data->number;
                                        } else {
                                            $page = \common\models\IntegrationInvoice::pageOrder($data->order_id);
                                            return (!empty($data->order_id)) ? \yii\helpers\Html::a($data->number, ['/clientintegr/' . $link . '/waybill/index', 'way' => $data->order_id, 'page' => $page]) : $data->number;
                                        }
                                    }
                                ],
                                [
                                    'attribute'          => 'date',
                                    'format'             => 'raw',
                                    'filterInputOptions' => [
                                        'class'       => 'form-control',
                                        'placeholder' => 'Дата'
                                    ],
                                    'value'              => function ($row) {
                                        return \Yii::$app->formatter->asDatetime(new DateTime($row->date), 'php:Y-m-d');
                                    }
                                ],
                                [
                                    'format' => 'raw',
                                    'header' => 'Наименование поставщика',
                                    'value'  => function ($data) {
                                        return $data->name_postav;
                                    }
                                ],
                                [
                                    'attribute' => 'consignee',
                                    'value'     => function ($data) {
                                        if ($data->consignee) {
                                            return $data->consignee;
                                        } else {
                                            return $data->organization->name;
                                        }
                                    }
                                ],
                                [
                                    'attribute' => 'count',
                                    'value'     => function ($data) {
                                        return count($data->content);
                                    }
                                ],
                                [
                                    'format'    => 'raw',
                                    'attribute' => 'created_at',
                                    'value'     => function ($data) {
                                        return Yii::$app->formatter->asDatetime($data->created_at, "php:Y-m-d H:i:s");
                                    },
                                ],
                                [
                                    'format'         => 'raw',
                                    'attribute'      => 'order_id',
                                    'contentOptions' => function ($data) {
                                        return ["id" => "oid" . $data->id];
                                    },
                                    'value'          => function ($data) {
                                        return Html::a($data->order_id, Url::to(['/order/view', 'id' => $data->order_id]), ['class' => 'target-blank', 'data-pjax' => "0"]);
                                    },
                                ],
                                [
                                    'attribute' => 'total_sum_withtax',
                                    'value'     => function ($data) {
                                        return number_format($data->total_sum_withtax, 2, '.', ' ');
                                    }
                                ],
                                [
                                    'header'         => 'Связь с поставщиком',
                                    //'class' => 'yii\grid\ActionColumn',
                                    //'template' => '{view_relations}',
                                    //'contentOptions' => ['style' => 'text-align:center'],
                                    'hAlign'         => 'center',
                                    'contentOptions' => function ($data) {
                                        return ["id"          => "way" . $data->id,
                                                "data-vendor" => $data->vendor_id];
                                    },
                                    /* 'buttons' => [
                                      'view_relations' => function ($url, $model) {
                                      if (isset($model->order->vendor)) {
                                      return $model->order->vendor->name;
                                      } else {
                                      return 'Задать'renderButton($model->id);
                                      }
                                      }
                                      ], */
                                    'value'          => function ($model) {
                                        if (isset($model->vendor_id)) {
                                            return $model->vendor->name;
                                        } else {
                                            return 'Задать';
                                        }
                                    }
                                ],
                                [
                                    'class'            => 'kartik\grid\ExpandRowColumn',
                                    'width'            => '50px',
                                    'value'            => function ($model, $key, $index, $column) {
                                        return GridView::ROW_COLLAPSED;
                                    },
                                    'detail'           => function ($model, $key, $index, $column) {
                                        return \Yii::$app->controller->renderPartial('_content', ['model' => $model]);
                                    },
                                    'expandOneOnly'    => false,
                                    'enableRowClick'   => false,
                                    'allowBatchToggle' => false,
                                    'contentOptions'   => function ($data) {
                                        return ["id" => "triangle" . $data->id];
                                    }
                                ],
                            ]
                        ]);
                    } catch (Exception $e) {
                        die($e);
                    }
                    ?>
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

if (!empty($integration)) {
    $links = [
        'rk'       => [
            'title' => 'R-Keeper',
            'url'   => '/clientintegr/rkws/waybill/index'
        ],
        'iiko'     => [
            'title' => 'iiko Office',
            'url'   => '/clientintegr/iiko/waybill/index'
        ],
        'tillypad' => [
            'title' => 'Tillypad',
            'url'   => '/clientintegr/tillypad/waybill/index'
        ]
    ];
    foreach ($integration as $key => $row) {
        $list_integration .= '<br>' . \yii\helpers\Html::a($links[$key]['title'], \Yii::$app->urlManager->createUrl($links[$key]['url']), [
                'class' => 'btn btn-primary'
            ]);
    }
}

$js = <<<JS
    $(function () {
        function links_column9 () {
            $('[data-col-seq='+9+']').each(function() {
                var idtd = $(this).attr('id');
                var idtds = String(idtd);
                var idnumber = idtds.substring(3);
                var idbutton = 'but' + idnumber;
                var cont_old = $(this).html();
                var oid = $('#oid'+idnumber+' a').html();
                if (oid=='') {cont_old='<i>'+cont_old+'</i>';}
                var cont_new = '<button type="button" class="button_name" id="'+idbutton+'" style="background:none;color:red;border:none;border-bottom:1px dashed">'+cont_old+'</button>';
                if (idbutton!='butefined') {
                    if (oid=='') {
                        $(this).html(cont_new);
                    }
                }
                
                $('.button_name').on('click', function () {
                var idtd = $(this).attr('id');
                var idtds = String(idtd);
                var idnumber = idtds.substring(3);
                var invoice_id = idnumber;
                var td = $(this).parents('tr').find('td:last-child');
                var this_ = $(this);
                var organization_id = $organization->id;
                    swal({
                html: '<input type="text" id="bukv-postav" class="swal2-input" placeholder="Введите или выберите поставщика" autofocus>'+'<div id="bukv-postav2" style="margin-top:0px;padding-top:0px;"></div>'+'<div id="bukv-postav3" style="margin-top:0px;padding-top:0px;"></div>',
                inputPlaceholder: 'Введите или выберите поставщика',
                confirmButtonText: 'Выбрать',
                cancelButtonText: 'Отмена',
                showCancelButton: true,
                title: 'Выберите поставщика',
                inputOptions: new Promise(function (resolve) {
                    $(document).ready ( function(){
                        $("#bukv-postav").focus();
                        var a = $("#bukv-postav").val();
                        $.post('$url/list-postav', {org_id: organization_id, stroka: a}).done(
                                    function(data){
                                        var arr = JSON.parse(data);
                                        if (arr.length>0) {
                                            var sel100 = 'Показаны первые 100 позиций';
                                            if (arr.length>=100) {
                                            $('#bukv-postav3').html(sel100);
                                        }
                                            var sel = '<div id="spisok">';
                                            sel = sel+'<select id="selpos" name="list_postav" class="swal2-input">';
                                            var index;
                                            for (index = 0; index < arr.length; ++index) {
                                                sel = sel+'<option value="'+arr[index]['id']+'">'+arr[index]['name']+'</option>';
                                            }
                                            sel = sel+'</select></div>';
                                        } else {
                                            sel = 'Нет данных.';
                                        }
                                        $('#bukv-postav2').html(sel);
                                        $('#bukv-postav').css("margin-bottom", "0px");
                                        $('#selpos').css("margin-top", "0px");
                                });
                        $("#bukv-postav").keyup(function() {
                            var a = $("#bukv-postav").val();
                                    $.post('$url/list-postav', {org_id: organization_id, stroka: a}).done(
                                    function(data){
                                        var arr = JSON.parse(data);
                                        if (arr.length>0) {
                                            var sel = '<div id="spisok">';
                                            sel = sel+'<select id="selpos" name="list_postav" class="swal2-input">';
                                            var index;
                                            for (index = 0; index < arr.length; ++index) {
                                                sel = sel+'<option value="'+arr[index]['id']+'">'+arr[index]['name']+'</option>';
                                            }
                                            sel = sel+'</select></div>';
                                        } else {
                                            sel = 'Нет данных.';
                                        }
                                        $('#bukv-postav2').html(sel);
                                        $('#bukv-postav').css("margin-bottom", "0px");
                                        $('#selpos').css("margin-top", "0px");
                                });
                        })
                    })
                })
            }
        ).then(function (result) {
            if(result.value) {
                var selectd = $("#selpos").val();
                if (selectd) {
                    var selected_name = $("#selpos option:selected").text();
                    if (selectd!=-1) {
                        $.post('$url/set-vendor', {
                            vendor_search_id: selectd,
                            vendor_id: selectd,
                            invoice_id: invoice_id
                        }, function (data) {
                            $('#but'+idnumber).html('<i>'+selected_name+'</i>');
                            $('#way'+idnumber).attr('data-vendor',selectd);
                            $('#rbutton'+idnumber).html('<a href="#" class="btn btn-secondary btn-lg invoice_radio" role="button" aria-disabled="true" id="'+idnumber+'">☐</a>');
                            radio_column1();
                        });
                    }
                }
            }
        });
                })
            });
        };
        
        function radio_column1 () {
            $('.invoice_radio').on('click', function () {
                var idnumber = $(this).attr('id');
                var invoice_number = $('#nn'+idnumber).text();
                var selectd = $('#way'+idnumber).attr('data-vendor');
                $('.orders').hide();
                
                $('[data-col-seq='+0+']').each(function() {
                    var ert = $(this).find("a");
                    var ert2 = ert.text();
                    if (ert2=='☑') ert.text('☐');
                })
                
                $('#'+idnumber).text('☑');
                $('#save-button').removeAttr('disabled', false);
                $.get('$url/get-orders-torg12', {
                    OrderSearch: {vendor_search_id: selectd, vendor_id: selectd},
                    invoice_id: invoice_number
                    }, function (data) {
                        $('#invoice-orders').html(data);
                        $('.orders').show();
                        $('#invoice-orders').ajaxComplete( function () {
                            history.pushState('', '', '/ru/clientintegr/email/invoice');
                        })
                    })
                
                $('#save-button').click(function () {

                    var button = $(this);

                    if(button.attr('disabled') === 'disabled') {
                        return;
                    }

                    button.attr('disabled', 'disabled');
                    button.html('Сохранение...');

                    var params = {};
                    var create_order = true;
                    //row_invoice = $('.invoice_radio:checked').parents('tr');
                    $('[data-col-seq='+0+']').each(function() {
                        var ert = $(this).find("a");
                        var ert2 = ert.text();
                        if (ert2=='☑') {
                            idnumber = ert.attr('id');
                        };
                    })
                    params.invoice_id = idnumber;
                    var selectd = $('#way'+idnumber).attr('data-vendor');
                    params.vendor_id = selectd;
                    params.order_id = $('.orders_radio:checked').val();
                    
                    if (params.order_id === undefined) {
                        create_order = false;
                        swal({
                            title: 'Будет создан новый заказ?',
                            text: "Вы уверены, что не хотите прикрепить накладную к существующему заказу?",
                            type: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Продолжить',
                            cancelButtonText: 'Отмена',
                        }).then(function (result) {
                            if(result.dismiss == 'cancel'){
                                button.removeAttr('disabled', false);
                                button.html('<i class="fa fa-save"></i> Сохранить');
                                return false;
                            }
                            $.post('$url/create-order', params, function (data) {
                                if (data.status === true) {
                                $('.catalog-index.orders').hide();
                                var number_order = data.order_id;
                                var us = data.us;
                                var page = data.page;
                                $('#rbutton'+idnumber).html('');
                                $('#oid'+idnumber).html('<a class="target-blank" href="/ru/order/'+number_order+'" data-pjax="0">'+number_order+'</a>');
                                var nn_old = $('#nn'+idnumber).html();
                                var nn_new = '<a href="/ru/clientintegr/'+us+'/waybill/index?way='+number_order+'&page='+page+'">'+nn_old+'</a>';
                                $('#nn'+idnumber).html(nn_new);
                                var ssp = $('#but'+idnumber).text();
                                $('#way'+idnumber).html(ssp);
                                //$(row_invoice).find('.invoice_radio').remove();
                                swal(
                                    'Накладная успешно привязана!',
                                    'Перейти в интеграцию: $list_integration',
                                    'success'
                                );
                                } else {
                                    errorSwal(data.error)
                                }
                                button.html('<i class="fa fa-save"></i> Сохранить');
                            });
                        });
                    } else {
                        if (create_order === true) {
                            $.post('$url/create-order', params, function (data) {
                                if (data.status === true) {
                                $('input[class="orders_radio"]').prop('checked', false);
                                $('.catalog-index.orders').hide();
                                var number_order = data.order_id;
                                var us = data.us;
                                $('#rbutton'+idnumber).html('');
                                $('#oid'+idnumber).html('<a class="target-blank" href="/ru/order/'+number_order+'" data-pjax="0">'+number_order+'</a>');
                                var nn_old = $('#nn'+idnumber).html();
                                var nn_new = '<a href="/ru/clientintegr/'+us+'/waybill/index?way='+number_order+'">'+nn_old+'</a>';
                                $('#nn'+idnumber).html(nn_new);
                                var ssp = $('#but'+idnumber).text();
                                $('#way'+idnumber).html(ssp);
                                //$(row_invoice).find('.invoice_radio').remove();
                                swal(
                                    'Накладная успешно привязана!',
                                    'Перейти в интеграцию: $list_integration',
                                    'success'
                                );
                                } else {
                                    errorSwal(data.error)
                                }
                                button.html('<i class="fa fa-save"></i> Сохранить');
                            });
                        }
                    }
                });
            })
        }
        
        function krestik () {
            $('.box-body').on('click', '.clear_invoice_radio', function () {
                $('[data-col-seq='+0+']').each(function() {
                    var ert = $(this).find("a");
                    var ert2 = ert.text();
                    if (ert2=='☑') ert.text('☐');
                })
                $('.orders').hide();
                $('#save-button').attr('disabled', 'disabled');
            });
        }
        
        function triangle () {
            $('[data-col-seq='+10+']').each(function() {
                $(this).on('click', function () {
                    var id_td = $(this).attr('id');
                    var number = id_td.substring(8);
                    var est_raskr = $('td').hasClass('raskr');
                    if (est_raskr===true) {
                        var id_td_raskr = $('.raskr').attr('id');
                        if (id_td==id_td_raskr) {
                            var ht_raskr = $('#vrem2').html();
                            $('#'+id_td+' div .kv-expand-detail').html(ht_raskr);
                            $('#vrem').remove();
                            $(this).removeClass('raskr');
                        } else {
                            var ht_raskr = $('#vrem2').html();
                            $('#'+id_td_raskr+' div .kv-expand-detail').html(ht_raskr);
                            $('#vrem').remove();
                            $('#'+id_td_raskr).removeClass('raskr');
                            var ht2 = $('#'+id_td+' div .kv-expand-detail').html();
                            var num_str = $('#triangle'+number+' .kv-expanded-row').attr('data-index');
                            var tabl = '<tr id="vrem" class="kv-expand-detail-row info skip-export" data-key="'+number+'" data-index="'+num_str+'"><td id="vrem2" colspan="11">'+ht2+'</td></tr>';
                            $('#'+id_td+' div .kv-expand-detail').html('');
                            $(this).addClass('raskr');
                            $(this).parents('tr').after(tabl);
                        }
                    } else {
                        var ht2 = $('#'+id_td+' div .kv-expand-detail').html();
                        var num_str = $('#triangle'+number+' .kv-expanded-row').attr('data-index');
                        var tabl = '<tr id="vrem" class="kv-expand-detail-row info skip-export" data-key="'+number+'" data-index="'+num_str+'"><td id="vrem2" colspan="11">'+ht2+'</td></tr>';
                        $('#'+id_td+' div .kv-expand-detail').html('');
                        $(this).addClass('raskr');
                        $(this).parents('tr').after(tabl);
                    }
                });
            });
        }
        
    $(document).ready(function() {
        links_column9();
        radio_column1();
        krestik();
    });
        
    $('.box-body').on('click', '.clear_radio', function () {
        $('.orders_radio').prop('checked', false);
    });

    /*$('.box-body').on('click', '.clear_invoice_radio', function () {
        $('[data-col-seq='+0+']').each(function() {
                    var ert = $(this).find("a");
                    var ert2 = ert.text();
                    if (ert2=='☑') ert.text('☐');
                })
                
        $('.orders').hide();
        $('#save-button').attr('disabled', 'disabled');
    });*/
    
    $(document).on('pjax:end', function() {
        links_column9();
        radio_column1();
        krestik();
        triangle();
    });
    
    /*$('#save-button').click(function () {

        var button = $(this);

        if(button.attr('disabled') === 'disabled') {
            alert('Идёт обработка накладной, подождите...');
            return;
        }

        button.attr('disabled', 'disabled');
        button.html('Сохранение...');

        var params = {};
        var create_order = true;
        row_invoice = $('.invoice_radio:checked').parents('tr');
        params.invoice_id = $(row_invoice).find('.invoice_radio:checked').val();
        params.vendor_id = $(row_invoice).find('.view-relations').data('vendor_id');
        params.order_id = $('.orders_radio:checked').val();*/

        /*if (params.invoice_id === undefined) {
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
        }*/

        /*if (params.order_id === undefined) {
            create_order = false;
            swal({
                title: 'Будет создан новый заказ?',
                text: "Вы уверены, что не хотите прикрепить накладную к существующему заказу.",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Продолжить',
                cancelButtonText: 'Отмена',
            }).then(function (result) {
                if(result.dismiss == 'cancel'){
                    button.removeAttr('disabled', false);
                    button.html('<i class="fa fa-save"></i> Сохранить');
                    return false;
                }
                $.post('$url/create-order', params, function (data) {
                if (data.status === true) {
                    $('input[class="orders_radio"]').prop('checked', false);
                    $('.catalog-index.orders').hide();
                    $(row_invoice).find('.invoice_radio').remove();
                    swal(
                    'Накладная успешно привязана!',
                    'Перейти в интеграцию: $list_integration',
                    'success'
                    );
                } else {
                    errorSwal(data.error)
                }
                button.removeAttr('disabled', false);
                button.html('<i class="fa fa-save"></i> Сохранить');
                });
            });
        }else{
                if (create_order === true) {
                    $.post('$url/create-order', params, function (data) {
                        if (data.status === true) {
                        $('input[class="orders_radio"]').prop('checked', false);
                        $('.catalog-index.orders').hide();
                        $(row_invoice).find('.invoice_radio').remove();
                        swal(
                        'Накладная успешно привязана!',
                        'Перейти в интеграцию: $list_integration',
                        'success'
                        );
                        } else {
                        errorSwal(data.error)
                        }
                        button.removeAttr('disabled', false);
                        button.html('<i class="fa fa-save"></i> Сохранить');
                    });
                }
            }
    });*/

    function errorSwal(message) {
        swal(
            'Ошибка',
            message,
            'error'
        )
    }
    });
            
    /*$('.view-relations').click(function () {
        var invoice_id = $(this).data('invoice_id');
        var td = $(this).parents('tr').find('td:last-child');
        var this_ = $(this);
        var organization_id = $organization->id;

        swal({
                html: '<input type="text" id="bukv-postav" class="swal2-input" placeholder="Введите или выберите поставщика" autofocus>'+'<div id="bukv-postav2" style="margin-top:0px;padding-top:0px;"></div>'+'<div id="bukv-postav3" style="margin-top:0px;padding-top:0px;"></div>',
                //inputPlaceholder: 'Введите хотя бы две буквы',Введите хотя бы две буквы названия поставщика
                inputPlaceholder: 'Введите или выберите поставщика',
                confirmButtonText: 'Выбрать',
                cancelButtonText: 'Отмена',
                showCancelButton: true,
                title: 'Выберите поставщика',
                inputOptions: new Promise(function (resolve) {
                    $(document).ready ( function(){
                        $("#bukv-postav").focus();
                        var a = $("#bukv-postav").val();
                        $.post('$url/list-postav', {org_id: organization_id, stroka: a}).done(
                                    function(data){
                                        var arr = JSON.parse(data);
                                        if (arr.length>0) {
                                            var sel100 = 'Показаны первые 100 позиций';
                                            if (arr.length>=100) {
                                            $('#bukv-postav3').html(sel100);
                                        }
                                            var sel = '<div id="spisok">';
                                            sel = sel+'<select id="selpos" name="list_postav" class="swal2-input">';
                                            var index;
                                            for (index = 0; index < arr.length; ++index) {
                                                sel = sel+'<option value="'+arr[index]['id']+'">'+arr[index]['name']+'</option>';
                                            }
                                            sel = sel+'</select></div>';
                                        } else {
                                            sel = 'Нет данных.';
                                        }
                                        $('#bukv-postav2').html(sel);
                                        $('#bukv-postav').css("margin-bottom", "0px");
                                        $('#selpos').css("margin-top", "0px");
                                });
                        $("#bukv-postav").keyup(function() {
                            var a = $("#bukv-postav").val();
                                //if (a.length>=0) {
                                    $.post('$url/list-postav', {org_id: organization_id, stroka: a}).done(
                                    function(data){
                                        var arr = JSON.parse(data);
                                        if (arr.length>0) {
                                            var sel = '<div id="spisok">';
                                            sel = sel+'<select id="selpos" name="list_postav" class="swal2-input">';
                                            var index;
                                            for (index = 0; index < arr.length; ++index) {
                                                sel = sel+'<option value="'+arr[index]['id']+'">'+arr[index]['name']+'</option>';
                                            }
                                            sel = sel+'</select></div>';
                                        } else {
                                            sel = 'Нет данных.';
                                        }
                                        $('#bukv-postav2').html(sel);
                                        $('#bukv-postav').css("margin-bottom", "0px");
                                        $('#selpos').css("margin-top", "0px");
                                });
                            //} else {
                              //  $('#bukv-postav2').text('');
                            //}
                        })
                    })
                })
            }
        ).then(function (result) {
            if(result.value) {
                var selectd = $("#selpos").val();
                if (selectd) {
                    var selected_name = $("#selpos option:selected").text();
                    console.log(selected_name);
                    if (selectd!=-1) {
                        $.get('$url/get-orders', {
                            OrderSearch: {vendor_search_id: selectd, vendor_id: selectd},
                            invoice_id: invoice_id
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
    });*/

    
    
JS;
$this->registerJs($js);
$this->registerJsFile(
    'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]
);
?>
</div>
