<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\grid\GridView;
use yii\widgets\Pjax;
?>
<?php
$grid = [
    [
        'label' => false,
        'format' => 'raw',
        'value' => function ($data) {
            if ($data['type_id'] == \common\models\Organization::TYPE_RESTAURANT) {
                return "<span style='color: #cacaca;'>" . Yii::t('message', 'frontend.views.user.default.buyer', ['ru' => 'Закупщик']) . " </span><br><span style='color:#84bf76'><b>" . $data['name'] . "</b></span>";
            }
            return "<span style='color: #cacaca;'>" . Yii::t('message', 'frontend.views.user.default.vendor', ['ru' => 'Поставщик']) . " </span><br><span style='color:#84bf76'><b>" . $data['name'] . "</b></span>";
        },
    ],
    [
        'label' => false,
        'format' => 'raw',
        'value' => function ($data) {
            if ($data['id'] == \common\models\User::findIdentity(Yii::$app->user->id)->organization_id) {

                return Html::a('<i class="fa fa-toggle-on"  style="margin-top:8px;"></i>', '#', [
                            'class' => 'disabled pull-right',
                            'style' => 'font-size:26px;color:#84bf76;padding-right:25px;'
                ]);
            }
            return Html::a('<i class="fa fa-toggle-on" style="transform: scale(-1, 1);margin-top:8px;"></i>', '#', [
                        'class' => 'change-net-org pull-right',
                        'style' => 'font-size:26px;color:#ccc;padding-right:25px;',
                        'data' => ['id' => $data['id']],
            ]);
        },
    ],
];
?>
<style>

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
                        <h5><?= Yii::t('message', 'frontend.views.user.default.business_list', ['ru' => 'Список бизнесов']) ?></h5>
                        <div class="network-list">
                            <?php Pjax::begin(['id' => 'pjax-network-list', 'enablePushState' => false, 'timeout' => 10000]) ?>
                            <?=
                            GridView::widget([
                                'dataProvider' => $dataProvider,
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