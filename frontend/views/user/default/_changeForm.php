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
    'label'=>false,
    'format' => 'raw',
    'value'=>function ($data) {
            if($data['type_id']==\common\models\Organization::TYPE_RESTAURANT){
            return "<span style='color: #cacaca;'>Закупщик</span><br><span style='color:#84bf76'><b>" . $data['name'] . "</b></span>";
            }        
        return "<span style='color: #cacaca;'>Поставщик</span><br><span style='color:#84bf76'><b>" . $data['name'] . "</b></span>";
        },
    ],
    [
    'label'=>false,
    'format' => 'raw',
    'value'=>function ($data) {
            if($data['id'] == \common\models\User::findIdentity(Yii::$app->user->id)->organization_id){
    
    return  Html::a('<i class="fa fa-toggle-on"  style="margin-top:8px;"></i>', '#', [
                'class' => 'disabled pull-right',
                'style' => 'font-size:26px;color:#84bf76;padding-right:25px;'
            ]);}
    return  Html::a('<i class="fa fa-toggle-on" style="transform: scale(-1, 1);margin-top:8px;"></i>', '#', [
                'class' => 'change-net-org pull-right',
                'style' => 'font-size:26px;color:#ccc;padding-right:25px;',
                'data' => ['id' => $data['id']],
            ]);          
        },
    ], 
];
?>
<div class="modal-body network-modal">
    <div class="row">
        <div class="col-md-12">
            <h3 class="pull-left">БИЗНЕС <span style="color:#84bf76;margin-top:5px;"><?=$user->organization->name;?></span></h3>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true" style="padding-bottom: 10px;">×</button>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <p style="color:#BAB9B9">Выберите из имеющихся для доступа в Ваш бизнес-профиль или создайте новый</p>
            <hr>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <h5>Список бизнесов</h5>
            <div class="network-list">
            <?php Pjax::begin(['id' => 'pjax-network-list', 'enablePushState' => false,'timeout' => 10000])?>
            <?=GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterPosition' => false,
                    'columns' => $grid, 
                    'options' => ['class' => 'table-responsive'],
                    'tableOptions' => ['class' => 'table dataTable', 'role' => 'grid'],
                    'bordered' => false,
                    'striped' => false,
                    'summary' => false,
                    'condensed' => false,
                    'showHeader'=>false,
                    'resizableColumns'=>false,

            ]);
            ?> 
            <?php Pjax::end(); ?> 
            </div>
        </div>
        <div class="col-md-4">
          <h5>Создать бизнес</h5>
            <?php
            $form = ActiveForm::begin([
                        'id' => 'create-network-form',
                        'action' => Url::to('/user/default/create'),
            ]);
            ?>
            <?=
                    $form->field($organization, 'type_id')
                    ->radioList(
                            [\common\models\Organization::TYPE_RESTAURANT => ' Закупщик', \common\models\Organization::TYPE_SUPPLIER => ' Поставщик'], 
                            [
                                'item' => function($index, $label, $name, $checked, $value) use ($organization) {

                                    $checked = $checked ? 'checked' : '';
                                    $return = '<label>';
                                    $return .= '<input type="radio" name="' . $name . '" value="' . $value . '" '.$checked.'>';
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
                    ->textInput(['class' => 'form-control', 'placeholder' => 'Название организации']);
            ?>
            <?= Html::submitButton('Создать бизнес', ['class' => 'btn btn-md btn-success new-network']) ?>
          <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
