<?php

use yii\widgets\Breadcrumbs;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\form\ActiveForm;
use kartik\grid\GridView;
use common\models\Organization;

$this->title = 'Интеграция с Tillypad';

?>
<section class="content-header">
    <h1>
        <i class="fa fa-upload"></i> <?= $this->title ?>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links'   => [
            [
                'label' => 'Интеграция',
                'url'   => ['/clientintegr'],
            ],
            $this->title
        ],
    ])
    ?>
</section>
<section class="content-header">
    <?= $this->render('/default/_menu.php'); ?>
    Контрагенты Tillypad
</section>
<section class="content">
    <div class="catalog-index">
        <div class="box box-info">
            <div class="box-header with-border">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6" align="left">
                            <div class="guid-header">
                                <?php
                                $form = ActiveForm::begin([
                                    'action'  => Url::to(['agent-view']),
                                    'method'  => 'get',
                                    'options' => [
                                        'id'    => 'searchForm',
                                        //'data-pjax' => true,
                                        'class' => "navbar-form no-padding no-margin",
                                        'role'  => 'search',
                                    ],
                                ]);
                                ?>
                                <?php echo
                                $form->field($searchModel, 'searchString', [
                                    'addon'   => [
                                        'append' => [
                                            'content' => '<a class="btn-xs"><i class="fa fa-search"></i></a>',
                                            'options' => [
                                                'class' => 'append',
                                            ],
                                        ],
                                    ],
                                    'options' => [
                                        'class' => "margin-right-15 form-group",
                                    ],
                                ])
                                    ->textInput([
                                        'id'          => 'searchString',
                                        'class'       => 'form-control',
                                        'placeholder' => Yii::t('message', 'frontend.views.order.search_two', ['ru' => 'Поиск']),
                                    ])
                                    ->label(Yii::t('message', 'frontend.views.contragent.name', ['ru' => 'Наименование контрагента']), ['class' => 'label', 'style' => 'color:#555']);
                                ?>
                                <?php echo
                                $form->field($searchModel, 'noComparison', [
                                    'options' => [
                                        'class' => "margin-right-15 form-group",
                                    ],
                                ])
                                    ->checkbox([
                                        'id'      => 'noComparison',
                                        'inline'  => true,
                                        'uncheck' => '0',
                                    ])
                                    ->label(Yii::t('message', 'frontend.views.contragent.only.nocomparison', ['ru' => 'Показывать только несопоставленных контрагентов']), ['class' => 'label', 'style' => 'color:#555']);
                                ?>
                                <?php ActiveForm::end(); ?>
                            </div>
                        </div>
                    </div>
                    <div class="box-body table-responsive no-padding">
                        <?php \yii\widgets\Pjax::begin(['formSelector' => 'form', 'enablePushState' => false, 'timeout' => 5000]) ?>
                        <?php

                        echo GridView::widget([
                            'dataProvider'     => $dataProvider,
                            'pjax'             => false,
                            'filterPosition'   => false,
                            'options'          => ['class' => 'table-responsive'],
                            'tableOptions'     => ['class' => 'table table-bordered table-striped dataTable', 'role' => 'grid'],
                            'formatter'        => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '---'],
                            'bordered'         => false,
                            'striped'          => true,
                            'condensed'        => false,
                            'responsive'       => false,
                            'hover'            => true,
                            'resizableColumns' => false,
                            'export'           => [
                                'fontAwesome' => true,
                            ],
                            'columns'          => [
                                'id',
                                [
                                    'attribute'      => 'denom',
                                    'label'          => 'Контрагент Tillypad',
                                    'contentOptions' => function ($model) {
                                        return ["id" => "agent" . $model['id']];
                                    },
                                ],
                                [
                                    'attribute'      => 'vendor_id',
                                    'label'          => 'Поставщик MixCart',
                                    'vAlign'         => 'middle',
                                    'width'          => '210px',
                                    'contentOptions' => function ($model) {
                                        return ["id" => "mixct" . $model['id']];
                                    },
                                    'value'          => function ($model) {
                                        return Organization::get_value($model->vendor_id)->name ?? '(не задано)';
                                    },
                                ],
                                [
                                    'attribute'      => 'comment',
                                    'label'          => 'Комментарий',
                                    'contentOptions' => function ($model) {
                                        return ["id" => "commt" . $model['id']];
                                    },
                                ],
                                [
                                    'attribute'      => 'is_active',
                                    'label'          => 'Статус активности',
                                    'contentOptions' => function ($model) {
                                        return ["id" => "activ" . $model['id']];
                                    },
                                    'value'          => function ($model) {
                                        if ($model['is_active'] == 1) {
                                            return 'Активен';
                                        } else {
                                            return 'Не активен';
                                        }
                                    }
                                ],
                                [
                                    'attribute' => 'created_at',
                                    'label'     => 'Создано',
                                ],
                                [
                                    'attribute' => 'updated_at',
                                    'label'     => 'Обновлено',
                                ],
                            ],
                        ]);
                        ?>
                        <?php \yii\widgets\Pjax::end() ?>
                        <?= Html::a('Вернуться', ['/clientintegr/tillypad/default'], ['class' => 'btn btn-success btn-export']) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
$url_auto_complete_new = Url::toRoute('sync/agent-autocomplete');
$url_edit_new = Url::toRoute('sync/edit-vendor');
$url_edit_comment = Url::toRoute('sync/edit-comment');
$url_edit_active = Url::toRoute('sync/edit-active');

$js = <<< JS
    $(function () {
        function links_column_mixcart () { // реакция на нажатие строки в столбце "Наименование продукта"
            $('[data-col-seq='+2+']').each(function() {
                var idtd = $(this).attr('id');
                var idtds = String(idtd);
                var idnumber = idtds.substring(5);
                var idbutton = 'but' + idnumber;
                var cont_old = $(this).html();
                if (cont_old=='(не задано)') {cont_old='<em>'+cont_old+'</em>';}
                var cont_new = '<button class="button-name" id="'+idbutton+'" style="color:#6ea262;background:none;border:none;border-bottom:1px dashed">'+cont_old+'</button>';
                if (idbutton!='butined') {
                    $(this).html(cont_new);
                }
            });
            $('.button-name').on('click', function () {
                $('a .button-name').click(function(){ return false;});
                var idbutton = $(this).attr('id');
                var idbuttons = String(idbutton);
                var number = idbuttons.substring(3);   // идентификатор строки
                var tovar = $("#agent"+number).html(); // наименование контрагента
                var cont_old = $(this).html();         // содержание ячейки до форматирования
                var nesopost = '<em>(не задано)</em>';   // содержание несопоставленной ячейки
                swal({
                    html: '<span style="font-size:14px">Сопоставить контрагента</span></br></br><span id="tovar">поставщик</span></br></br>' +
                    '<input type="text" id="bukv-tovar" class="swal2-input" placeholder="Введите или выберите поставщика" autofocus>'+
                    '<div id="bukv-tovar2" style="margin-top:0px;padding-top:0px;"></div>'+'<div id="bukv-tovar3" style="margin-top:0px;padding-top:0px;"></div>'
                    +'<div id="bukv-tovar4" style="margin-top:0px;padding-top:0px;"></div>'
                    + '</br><input type="submit" name="denom_forever" id="denom_forever" class="btn btn-sm btn-primary butsubmit" value="Сопоставить и запомнить"> '
                    + '<input type="button" id="denom_close" class="btn btn-sm btn-outline-danger" value="Отменить">',
                    showConfirmButton:false,
                    inputOptions: new Promise(function (resolve) {
                        $(document).ready ( function(){
                            $("#bukv-tovar").focus();
                            var a = $("#bukv-tovar").val();
                            $("#tovar").html(tovar);
                            if (cont_old!=nesopost)
                            {
                                $("#bukv-tovar").attr( 'placeholder', cont_old);
                            }
                            var url_auto_complete_new = '$url_auto_complete_new';
                            $.post(url_auto_complete_new, {stroka: a}).done(
                                function(data){
                                    data = data.results;
                                    if (data.length>0) {
                                            var sel100 = 'Показаны первые 100 позиций';
                                            if (data.length>=100) {
                                                $('#bukv-tovar3').html(sel100);
                                            } else {
                                                $('#bukv-tovar3').html('');
                                            }
                                            var sel = '<div id="spisok">';
                                            sel = sel+'<select id="selpos" name="list_tovar" class="swal2-input">';
                                            var index;
                                            for (index = 0; index < data.length; ++index) {
                                                if (data[index]['name'] == cont_old) {
                                                    var app = ' selected';
                                                } else {
                                                    var app = '';
                                                }
                                                sel = sel+'<option value="'+data[index]['id']+'"'+app+'>'+data[index]['name']+'</option>';
                                            }
                                            sel = sel+'</select></div>';
                                    } else {
                                        sel = 'Нет данных.';
                                    }
                                    $('#bukv-tovar').css("margin-bottom", "0px");
                                    $('#bukv-tovar2').html(sel);
                                    $('#selpos').css("margin-top", "0px");
                                }
                            );
                            $("#bukv-tovar").keyup(function() {
                                var a = $("#bukv-tovar").val();
                                var url_auto_complete_new = '$url_auto_complete_new';
                                $.post(url_auto_complete_new, {stroka: a}).done(
                                    function(data){
                                        data = data.results;
                                        if (data.length>0) {
                                            var sel100 = 'Показаны первые 100 позиций';
                                            if (data.length>=100) {
                                                $('#bukv-tovar3').html(sel100);
                                            } else {
                                                $('#bukv-tovar3').html('');
                                            }
                                            var sel = '<div id="spisok">';
                                            sel = sel+'<select id="selpos" name="list_postav" class="swal2-input">';
                                            var index;
                                            for (index = 0; index < data.length; ++index) {
                                                if (data[index]['name'] == cont_old) {
                                                    var app = ' selected';
                                                } else {
                                                    var app = '';
                                                }
                                                sel = sel+'<option value="'+data[index]['id']+'"'+app+'>'+data[index]['name']+'</option>';
                                            }
                                            sel = sel+'</select></div>';
                                        } else {
                                            sel = 'Нет данных.';
                                        }
                                        $('#bukv-tovar').css("margin-bottom", "0px");
                                        $('#bukv-tovar2').html(sel);
                                        $('#selpos').css("margin-top", "0px");
                                    }
                                );
                            })
                        });
                    })
                })
                $('#denom_close').on('click', function() {
                    swal.close();
                })
                $('#denom_forever').on('click', function () {
                    var selectvalue = $('#selpos').val();
                    var selected_name = $("#selpos option:selected").text();
                    var url_edit_new = '$url_edit_new';
                    $.post(url_edit_new, {id:selectvalue, number:number}, function (data) {
                        if (data == true) {
                            $('#but'+number).html(selected_name);
                            swal.close();
                        } else {
                            alert('Не удалось сохранить контрагента Tillypad.');
                        }
                    })
                });
            });
        }
        
        function links_column_comment () { // реакция на нажатие строки в столбце "Комментарий"
            $('[data-col-seq='+3+']').each(function() {
                var idtd = $(this).attr('id');
                var idtds = String(idtd);
                var idnumber = idtds.substring(5);
                var idbutton = 'butcom' + idnumber;
                var cont_old = $(this).html();
                if (cont_old=='---') {cont_old='<em>'+cont_old+'</em>';}
                var cont_new = '<button class="button-comment" id="'+idbutton+'" style="color:#6ea262;background:none;border:none;border-bottom:1px dashed">'+cont_old+'</button>';
                if (idbutton!='butcomined') {
                    $(this).html(cont_new);
                }
            });
            $('.button-comment').on('click', function () {
                $('a .button-comment').click(function(){ return false;});
                var idbutton = $(this).attr('id');
                var idbuttons = String(idbutton);
                var number = idbuttons.substring(6);   // идентификатор строки
                var tovar = $("#agent"+number).html(); // наименование контрагента
                var cont_old = $(this).html();         // содержание ячейки до форматирования
                var nesopost = '<em>---</em>';   // содержание несопоставленной ячейки
                swal({
                    html: '<span style="font-size:14px">Оставить комментарий к контрагенту</span></br></br><span id="tovar">---</span></br></br>' +
                    '<input type="text" id="bukv-tovar" class="swal2-input" placeholder="Введите комментарий" autofocus>'+
                    '<div id="bukv-tovar2" style="margin-top:0px;padding-top:0px;"></div>'+'<div id="bukv-tovar3" style="margin-top:0px;padding-top:0px;"></div>'
                    +'<div id="bukv-tovar4" style="margin-top:0px;padding-top:0px;"></div>'
                    + '</br><input type="submit" name="denom_forever" id="denom_forever" class="btn btn-sm btn-primary butsubmit" value="Сохранить"> '
                    + '<input type="button" id="denom_close" class="btn btn-sm btn-outline-danger" value="Отменить">',
                    showConfirmButton:false,
                    inputOptions: new Promise(function (resolve) {
                        $(document).ready ( function(){
                            $("#bukv-tovar").focus();
                            var a = $("#bukv-tovar").val();
                            $("#tovar").html(tovar);
                            if (cont_old!=nesopost)
                            {
                                $("#bukv-tovar").attr( 'placeholder', cont_old);
                            }
                        });
                    })
                })
                $('#denom_close').on('click', function() {
                    swal.close();
                })
                $('#denom_forever').on('click', function () {
                    var selectvalue = $('#bukv-tovar').val();
                    var url_edit_comment = '$url_edit_comment';
                    if (selectvalue != '') {
                        $.post(url_edit_comment, {comm:selectvalue, number:number}, function (data) {
                            if (data == true) {
                                $('#butcom'+number).html(selectvalue);
                            } else {
                                alert('Не удалось сохранить комментарий к контрагенту Tillypad.');
                            }
                        })
                    }
                    swal.close();
                });
            });
        }
        
        function links_column_active () { // реакция на нажатие строки в столбце "Статус активности"
            $('[data-col-seq='+4+']').each(function() {
                var idtd = $(this).attr('id');
                var idtds = String(idtd);
                var idnumber = idtds.substring(5);
                var idbutton = 'butact' + idnumber;
                var cont_old = $(this).html();
                var cont_new = '<button class="button-active" id="'+idbutton+'" style="color:#6ea262;background:none;border:none;border-bottom:1px dashed">'+cont_old+'</button>';
                if (idbutton!='butactined') {
                    $(this).html(cont_new);
                }
            });
            $('.button-active').on('click', function () {
                $('a .button-active').click(function(){ return false;});
                var idbutton = $(this).attr('id');
                var idbuttons = String(idbutton);
                var number = idbuttons.substring(6);   // идентификатор строки
                var tovar = $("#agent"+number).html(); // наименование контрагента
                var cont_old = $(this).html();         // содержание ячейки до форматирования
                var nesopost = '<em>(не задано)</em>';   // содержание несопоставленной ячейки
                swal({
                    html: '<span style="font-size:14px">Изменить статус активности агента</span></br></br><span id="tovar"></span></br></br>' +
                    '<input type="text" id="bukv-tovar" class="swal2-input" placeholder="Введите статус активности агента" autofocus>'+
                    '<div id="bukv-tovar2" style="margin-top:0px;padding-top:0px;"></div>'+'<div id="bukv-tovar3" style="margin-top:0px;padding-top:0px;"></div>'
                    +'<div id="bukv-tovar4" style="margin-top:0px;padding-top:0px;"></div>'
                    + '</br><input type="submit" name="denom_forever" id="denom_forever" class="btn btn-sm btn-primary butsubmit" value="Сохранить"> '
                    + '<input type="button" id="denom_close" class="btn btn-sm btn-outline-danger" value="Отменить">',
                    showConfirmButton:false,
                    inputOptions: new Promise(function (resolve) {
                        $(document).ready ( function(){
                            $("#bukv-tovar").focus();
                            var a = $("#bukv-tovar").val();
                            $("#tovar").html(tovar);
                            if (cont_old!=nesopost)
                            {
                                $("#bukv-tovar").attr( 'placeholder', cont_old);
                            }
                            var sel = '<div id="spisok">';
                            sel = sel+'<select id="selpos" name="list_tovar" class="swal2-input">';
                            if (cont_old == 'Не активен') {
                                var app = ' selected';
                            } else {
                                var app = '';
                            }
                            sel = sel+'<option value="'+0+'"'+app+'>Не активен</option>';
                            if (cont_old == 'Активен') {
                                var app = ' selected';
                            } else {
                                var app = '';
                            }
                            sel = sel+'<option value="'+1+'"'+app+'>Активен</option>';
                            sel = sel+'</select></div>';
                            $('#bukv-tovar').css("margin-bottom", "0px");
                            $('#bukv-tovar2').html(sel);
                            $('#selpos').css("margin-top", "0px");
                        });
                    })
                })
                $('#denom_close').on('click', function() {
                    swal.close();
                })
                $('#denom_forever').on('click', function () {
                    var selectvalue = $('#selpos').val();
                    var selected_name = $("#selpos option:selected").text();
                    var url_edit_active = '$url_edit_active';
                    $.post(url_edit_active, {activ:selectvalue, number:number}, function (data) {
                        if (data == true) {
                            $('#butact'+number).html(selected_name);
                            swal.close();
                        } else {
                            alert('Не удалось изменить статус активности контрагента Tillypad.');
                        }
                    })
                });
            });
        }

        $(document).on('pjax:end', function() { // реакция на перерисовку грид-таблицы
            links_column_mixcart();
            links_column_comment();
            links_column_active();
            
            $(document).on("change", "#noComparison", function(e) { // реакция на изменение флажка Показывать только несопоставленных контрагентов
                if ($('#noComparison').is(':checked')){
                    $('#noComparison').val(1);
                } else {
                    $('#noComparison').val(0);
                }
                var form = $("#searchForm");
                form.submit();
            });

            $(document).on("change keyup paste cut", "#searchString", function() { // реакция на изменение строки в поисковом поле
                $("#hiddenSearchString").val($("#searchString").val());
                if (timer) {
                    clearTimeout(timer);
                }
                timer = setTimeout(function() {
                    $("#searchForm").submit();
                }, 700);
            });
            
        });

        $(document).ready(function() { // действия после полной загрузки страницы
            links_column_mixcart();
            links_column_comment();
            links_column_active();
            
            $(document).on("change", "#noComparison", function(e) { // реакция на изменение флажка Показывать только несопоставленных контрагентов
                if ($('#noComparison').is(':checked')){
                    $('#noComparison').val(1);
                } else {
                    $('#noComparison').val(0);
                }
                var form = $("#searchForm");
                form.submit();
            });

            $(document).on("change keyup paste cut", "#searchString", function() { // реакция на изменение строки в поисковом поле
                $("#hiddenSearchString").val($("#searchString").val());
                if (timer) {
                    clearTimeout(timer);
                }
                timer = setTimeout(function() {
                    $("#searchForm").submit();
                }, 700);
            });
        
        });
    });
JS;

$this->registerJs($js);
?>
