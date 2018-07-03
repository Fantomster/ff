<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\form\ActiveForm;
use kartik\grid\GridView;
use yii\widgets\Pjax;

\common\assets\SweetAlertAsset::register($this);

$deleteBusinessTitle = Yii::t('app', 'frontend.controllers.user.business_delete_question', ['ru' => "Действительно удалить бизнес?"]);
$cancelText = Yii::t('app', 'frontend.controllers.user.business_cancel_btn', ['ru' => "Отмена"]);
$confirmText = Yii::t('app', 'frontend.controllers.user.business_confirm_btn', ['ru' => "Удалить"]);
$errorTitle = Yii::t('app', 'frontend.controllers.user.business_error_title', ['ru' => "Ошибка!"]);
$errorText = Yii::t('app', 'frontend.controllers.user.business_error_text', ['ru' => "Произошла неизвестная ошибка"]);

$changeFormUrl = Url::to(['/user/default/change-form']);

$js = <<<JS
    $(document).on("click", ".btnSubmit", function() {
        $($(this).data("target-form")).submit();
    });
    $(document).on("focusout", "#searchString", function() {
        $('#searchForm').submit();
    });
    $(document).on("click", ".deleteBusiness", function(e) {
        e.preventDefault();
        var clicked = $(this);
        swal({
            title: "$deleteBusinessTitle",
            type: "warning",
            showCancelButton: true,
            cancelButtonText: "$cancelText",
            confirmButtonText: "$confirmText",
            showLoaderOnConfirm: true,
            allowOutsideClick: false,
            preConfirm: function (text) {
                return new Promise(function (resolve, reject) {
                    $.post(
                        clicked.data("url")
                    ).done(function (result) {
                        if (result) {
                            resolve(result);
                        } else {
                            resolve(false);
                        }
                    }).fail(function(result) {
                        });
                })
            },
        }).then(function (result) {
            if (result.dismiss === "cancel") {
                swal.close();
            } else if (result.value.type == "success") {
                $.pjax.reload({container: '#pjax-network-list', push:false, replace:false, timeout:30000, async: false, url: "$changeFormUrl"});
                swal(result.value);
            } else {
                swal({title: "$errorTitle", text: "$errorText", type: "error"});
            }
        });
    });
JS;
$this->registerJs($js, \yii\web\View::POS_READY);
$grid = [
    [
        'label' => false,
        'format' => 'raw',
        'value' => function ($data) {
            $rel = \common\models\RelationUserOrganization::findOne(['organization_id' => $data['id'], 'user_id' => Yii::$app->user->id]);
            if ($rel) {
                $role = \common\models\Role::findOne(['id' => $rel->role_id]);
                $roleName = " (" . Yii::t('app', $role->name) . ") ";
            } else {
                $roleName = '';
            }
            if ($data['type_id'] == \common\models\Organization::TYPE_RESTAURANT) {
                return "<span style='color: #cacaca;'>" . Yii::t('message', 'frontend.views.user.default.buyer', ['ru' => 'Закупщик']) . " </span><span style='color: #cacaca;'> $roleName </span><br><span style='color:#84bf76'><b>" . $data['name'] . "</b></span>";
            }
            return "<span style='color: #cacaca;'>" . Yii::t('message', 'frontend.views.user.default.vendor', ['ru' => 'Поставщик']) . " </span><span style='color: #cacaca;'> $roleName </span><br><span style='color:#84bf76'><b>" . $data['name'] . "</b></span>";
        },
    ],
    [
        'label' => false,
        'format' => 'raw',
        'value' => function ($data) {
            if ($data['id'] == \common\models\User::findIdentity(Yii::$app->user->id)->organization_id) {

                return Html::a('<i class="fa fa-toggle-on"  style="margin-top:8px;"></i>', '#', [
                            'class' => 'disabled pull-right',
                            'style' => 'font-size:26px;color:#84bf76;padding-right:10px;'
                ]);
            }
            return Html::a('<i class="fa fa-toggle-on" style="transform: scale(-1, 1);margin-top:8px;"></i>', '#', [
                        'class' => 'change-net-org pull-right',
                        'style' => 'font-size:26px;color:#ccc;padding-right:10px;',
                        'data' => ['id' => $data['id']],
            ]);
        },
        'contentOptions' => ['class' => 'text-center', 'style' => 'width: 50px;'],
    ],
    [
        'label' => false,
        'format' => 'raw',
        'value' => function ($data) {
            if ($data['id'] != \common\models\User::findIdentity(Yii::$app->user->id)->organization_id) {

                return Html::a('<i class="glyphicon glyphicon-trash"></i>', '#', [
                            'class' => 'btn btn-danger deleteBusiness',
                            'data-url' => Url::to(['/user/delete-business', 'id' => $data['id']]),
                            'data-pjax' => 0,
                ]);
            } else {
                return '';
            }
        },
        'contentOptions' => ['class' => 'text-center', 'style' => 'width: 50px;'],
    ],
];
?>
<style>
    #searchString{height:35px; width: 100%; display: inline-block;}
    .input-group, .form-group{width: 100%;}
    @media (max-width: 600px){
        .network-list .table {
            overflow-x: scroll;
            display: table;
        }
    }
    @media (max-width: 480px){
        .network-list .table a{float:none !important;padding: 0 !important;}
        .network-list .kv-table-wrap tr > td{border:0;}   
        .network-list .kv-table-wrap tr > td:last-child{border-bottom: 1px solid #ccc;}
        .network-list .kv-table-wrap tr:last-child > td:last-child{border-bottom: 0} 
        .network-list .kv-table-wrap th, .kv-table-wrap td {width: inherit !important; }
    }
</style>
<div id="changeBusinessModal" class="modal fade data-modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body network-modal">
                <div class="row">
                    <div class="col-md-12">
                        <h3 class="pull-left"><?= Yii::t('message', 'frontend.views.user.default.business', ['ru' => 'БИЗНЕС']) ?> <span style="color:#84bf76;margin-top:5px;"><?= $user->organization->name; ?></span></h3>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true" style="padding-bottom: 10px;">×</button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <p style="color:#BAB9B9"><?= Yii::t('message', 'frontend.views.user.default.choose', ['ru' => 'Выберите из имеющихся для доступа в Ваш бизнес-профиль или создайте новый']) ?></p>
                        <hr>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-8">


                        <br />
                        <?php
                        $form = ActiveForm::begin([
                                    'method' => 'get',
                                    'options' => [
                                        'id' => 'searchForm',
                                        'class' => "navbar-form no-padding no-margin",
                                        'role' => 'search',
                                    ],
                        ]);
                        ?>
                        <?=
                                $form->field($searchModel, 'searchString', [
                                    'addon' => [
                                        'append' => [
                                            'content' => '<a class="btn-xs btnSubmit" data-target-form="#searchForm"><i class="fa fa-search"></i></a>',
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
                                    'id' => 'searchString',
                                    'class' => 'form-control',
                                    'placeholder' => Yii::t('message', 'frontend.views.order.search', ['ru' => 'Поиск'])])
                                ->label(false)
                        ?>
                        <?php ActiveForm::end(); ?>


                        <h5><?= Yii::t('message', 'frontend.views.user.default.business_list', ['ru' => 'Список бизнесов']) ?></h5>
                        <div class="network-list">
                            <?php Pjax::begin(['formSelector' => '#searchForm', 'id' => 'pjax-network-list', 'enablePushState' => false, 'timeout' => 10000]) ?>
                            <?=
                            GridView::widget([
                                'dataProvider' => $dataProvider,
                                'filterModel' => $searchModel,
                                'filterPosition' => false,
                                'columns' => $grid,
                                'options' => [],
                                'tableOptions' => ['class' => 'table'],
                                'bordered' => false,
                                'striped' => false,
                                'summary' => false,
                                'condensed' => false,
                                'showHeader' => false,
                                'resizableColumns' => false,
                            ]);
                            ?> 
                            <?php Pjax::end(); ?> 
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h5><?= Yii::t('message', 'frontend.views.user.default.create', ['ru' => 'Создать бизнес']) ?></h5>
                        <?php
                        $form = ActiveForm::begin([
                                    'id' => 'create-network-form',
                                    'action' => Url::to(['/user/default/create']),
                        ]);
                        ?>
                        <?=
                                $form->field($organization, 'type_id')
                                ->radioList(
                                        [\common\models\Organization::TYPE_RESTAURANT => Yii::t('message', 'frontend.views.user.default.buyer_two', ['ru' => ' Закупщик']), \common\models\Organization::TYPE_SUPPLIER => Yii::t('message', 'frontend.views.user.default.vendor_two', ['ru' => ' Поставщик'])], [
                                    'item' => function($index, $label, $name, $checked, $value) use ($organization) {

                                        $checked = $checked ? 'checked' : '';
                                        $return = '<label>';
                                        $return .= '<input type="radio" name="' . $name . '" value="' . $value . '" ' . $checked . '>';
                                        $return .= '<i class="radio-ico"></i><span>' . $label . '</span>';
                                        $return .= '</label>';

                                        return $return;
                                    }
                                        ]
                                )
                                ->label(false);
                        ?>
                        <?=
                                $form->field($organization, 'name')
                                ->label(false)
                                ->textInput(['class' => 'form-control', 'placeholder' => Yii::t('message', 'frontend.views.user.default.org_name', ['ru' => 'Название организации'])]);
                        ?>
                        <?= Html::submitButton(Yii::t('message', 'frontend.views.user.default.create_business', ['ru' => 'Создать бизнес']), ['class' => 'btn btn-md btn-success new-network']) ?>
                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>