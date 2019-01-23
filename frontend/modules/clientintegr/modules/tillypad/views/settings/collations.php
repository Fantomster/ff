<?php

use yii\widgets\Breadcrumbs;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

?>
    <section class="content-header">
        <h1>
            <i class="fa fa-upload"></i> Интеграция с Tillypad
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
                'Интеграция с Tillypad',
            ],
        ])
        ?>
    </section>
    <section class="content-header">
        <?= $this->render('/default/_menu.php'); ?>
    </section>
    <section class="content">
        <div class="catalog-index">
            <div class="box box-info">
                <div class="box-header with-border">
                    <div class="panel-body">
                        <div class="box-body table-responsive no-padding" style="overflow-x:visible;">
                            <?php $form = ActiveForm::begin(); ?>
                            <div class="col-md-4">
                                <?php
                                $models = $provider->getModels();
                                $model = current($models);
                                $options = [];
                                $jsParentId = '';
                                $items = \yii\helpers\ArrayHelper::map($models, 'id', 'name');
                                if (!is_null($parentId)) {
                                    $jsParentId = $parentId->value;
                                    foreach ($items as $key => $value) {
                                        if ($jsParentId != $value) {
                                            $arTmp[$key] = ['disabled' => true];
                                        }
                                    }
                                    $arTmp[$jsParentId] = ['Selected' => true];
                                    $arTmp['readonly'] = true;
                                    $options = ['options' => $arTmp];
                                }

                                echo $form->field($model, 'name')->dropDownList($items, $options)->label('Укажите главный бизнес');
                                ?>
                                <?= \yii\helpers\Html::a('Применить сопоставление', false, ['class' => 'btn btn-md fk-button', 'id' => 'apply_collation']); ?>
                                <?= \yii\helpers\Html::a('Отменить всё', false, ['class' => 'btn btn-danger', 'id' => 'cancel_collation']); ?>
                            </div>
                            <div class="col-md-8" style="padding: 18px 0 0;">
                                <?=
                                \kartik\grid\GridView::widget([
                                    'dataProvider'     => $provider,
                                    'pjax'             => true,
                                    'summary'          => '',
                                    'filterPosition'   => false,
                                    'columns'          => [
                                        [
                                            'class'           => 'kartik\grid\CheckboxColumn',
                                            'checkboxOptions' => function ($model, $key, $index, $column) {
                                                $obConstModel = \api\common\models\iiko\iikoDicconst::findOne(['denom' => 'main_org']);
                                                $pConst = \api\common\models\iiko\iikoPconst::findOne(['const_id' => $obConstModel->id, 'org' => $key]);
                                                if (!is_null($pConst)) {
                                                    return [
                                                        'id'      => 'table-option-' . $key,
                                                        'checked' => true,
                                                    ];
                                                }
                                                return [
                                                    'id' => 'table-option-' . $key,
                                                ];
                                            }
                                        ],
                                        [
                                            'attribute' => 'name',
                                            'value'     => 'name',
                                            'label'     => 'Название организации',
                                        ],
                                    ],
                                    'options'          => ['class' => 'table-responsive'],
                                    'tableOptions'     => ['class' => 'table table-bordered table-striped dataTable', 'role' => 'grid'],
                                    'formatter'        => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
                                    'bordered'         => false,
                                    'striped'          => true,
                                    'condensed'        => false,
                                    'responsive'       => false,
                                    'hover'            => true,
                                    'resizableColumns' => false,
                                    'export'           => [
                                        'fontAwesome' => true,
                                    ],
                                ]);
                                ?>
                            </div>
                        </div>
                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php
$applyUrl = Url::toRoute('settings/apply-collation');
$cancelUrl = Url::toRoute('settings/cancel-collation');
$js = <<< JS
	$(function () {
        FF = {};
        FF.sendApplyCollation = {
        	init: function(){
        		$(document).on('click', '#apply_collation', function () {
		            var keys = $('#w1').yiiGridView('getSelectedRows'),
		                url = '$applyUrl';
		            
		            swal({
		                title: 'Выполнить массовое сопоставление для выделенных бизнесов?',
		                type: 'info',
		                showCancelButton: true,
		                confirmButtonColor: '#3085d6',
		                cancelButtonColor: '#d33',
		                confirmButtonText: 'Выполнить',
		                cancelButtonText: 'Отмена',
		            }).then((result) => {
		                if(result.value)
		                {
		                    swal({
		                        title: 'Идёт выполнение',
		                        text: 'Подождите, пока закончится выполнение...',
		                        onOpen: () => {
		                            swal.showLoading();
		                            $.post(url, {ids:keys, main:$('#organization-name').val()}, function (data) {
		                                console.log(data);
		                                if (data.success === true) {
		                                    swal.close();
		                                    swal('Готово', '', 'success');
		                                    location.reload();
		                                } else {
		                                    console.log(data.error);
		                                    swal(
		                                        'Ошибка',
		                                        data.error,
		                                        'error'
		                                    )
		                                }
		                                // $.pjax.reload({container:"#pjax_user_row_" + oid + '-pjax', timeout:1500});
		                            })
		                            .fail(function() {
		                               swal(
		                                    'Ошибка',
		                                    'Обратитесь в службу поддержки.',
		                                    'error'
		                                );
		                               // $.pjax.reload({container:"#pjax_user_row_" + oid + '-pjax', timeout:1500});
		                            });
		                        }
		                    })
		                }
		            })
		        });
        	}
        };
        
        FF.sendCancelCollation = {
        	init: function(){
        		$(document).on('click', '#cancel_collation', function () {
		            var trs = $('#w1 tbody > tr'),
		                ids = [],
		                url = '$cancelUrl';
		            
		            trs.map(function(index, el){
		            	ids.push($(el).data('key'));
		            });
		            
		            swal({
		                title: 'Выполнить удаление сопоставления для всех бизнесов?',
		                type: 'info',
		                showCancelButton: true,
		                confirmButtonColor: '#3085d6',
		                cancelButtonColor: '#d33',
		                confirmButtonText: 'Удалить',
		                cancelButtonText: 'Отмена',
		            }).then((result) => {
		                if(result.value)
		                {
		                    swal({
		                        title: 'Идёт удаление',
		                        text: 'Подождите, пока закончится удаление...',
		                        onOpen: () => {
		                            swal.showLoading();
		                            $.post(url, {ids:ids}, function (data) {
		                                
		                                if (data.success === true) {
		                                    swal.close();
		                                    swal('Готово', '', 'success');
	                                        location.reload();
		                                } else {
		                                    console.log(data.error);
		                                    swal(
		                                        'Ошибка',
		                                        data.error,
		                                        'error'
		                                    )
		                                }
		                                // $.pjax.reload({container:"#pjax_user_row_" + oid + '-pjax', timeout:1500});
		                            })
		                            .fail(function() {
		                               swal(
		                                    'Ошибка',
		                                    'Обратитесь в службу поддержки.',
		                                    'error'
		                                );
		                               // $.pjax.reload({container:"#pjax_user_row_" + oid + '-pjax', timeout:1500});
		                            });
		                        }
		                    })
		                }
		            })
		        });
        	}
        };
        
        FF.dropDownChange = {
        	init: function(){
        		var dropId = $('#organization-name').val(),
        		    mainOrg = "$jsParentId",
        		    firstEl = $('#table-option-' + dropId);
        		// console.log(mainOrg);
        		// return false;
        		firstEl.hide();
        		firstEl.prop('disabled', true);
        		firstEl.prop('checked', false);
        		firstEl.parent().parent().removeClass('danger');
        		$(document).on('change', '#organization-name', function (e) {
        			let option = '#table-option-';
        			$(option + dropId).show();
        			$(option + dropId).prop('checked', false);
        			$(option + dropId).prop('disabled', false);
        		    dropId = $(this).val();
        		    $(option + dropId).hide();
        		    $(option + dropId).prop('disabled', true);
        		    $(option + dropId).prop('checked', false);
        		    $(option + dropId).parent().parent().removeClass('danger');
        		});
        		
        		$(document).on('click', '#organization-name', function (e) {
        			if(mainOrg.length > 0){
        				e.preventDefault();
        				swal({
			                title: 'Нельзя сменить главный бизнес без сброса всех дочерних бизнесов!',
			                type: 'info',
			                confirmButtonColor: '#3085d6',
			                confirmButtonText: 'Ok',
		                });
        				return false;
        			}
        		});
            }
        };
        
        FF.sendCancelCollation.init();
        FF.sendApplyCollation.init();
        FF.dropDownChange.init();
    });

JS;

$this->registerJs($js);




