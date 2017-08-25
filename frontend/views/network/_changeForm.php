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
    'label'=>'Название',
    'format' => 'raw',
    'value'=>function ($data) {
            if($data->type_id==\common\models\Organization::TYPE_RESTAURANT){
            return "<span style='color:#84bf76'><b>" . $data->name . "</b></span> | Заведение";
            }        
        return "<span style='color:#84bf76'><b>" . $data->name . "</b></span> | Поставщик";
        },
    ],
    [
    'label'=>'',
    'format' => 'raw',
    'value'=>function ($data) {
            if($data->id == \common\models\User::findIdentity(Yii::$app->user->id)->organization_id){
    
    return  Html::a('<i class="fa fa-toggle-on" aria-hidden="true"></i> Активный', '#', [
                'class' => 'btn btn-gray disabled pull-right',
                'data' => ['id' => $data->id],
            ]);}
    return  Html::a('<i class="fa fa-toggle-on" aria-hidden="true"></i> Переключиться', '#', [
                'class' => 'btn btn-success change-net-org pull-right',
                'data' => ['id' => $data->id],
            ]);        
        },
    ], 
];
?>
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
    <h5 class="pull-left">СМЕНА БИЗНЕСА</h5>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true" style="padding-bottom: 10px;">×</button>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <p class="pull-left" style="color:#84bf76;margin-top:5px;"><?=$user->organization->name;?></p>
            <?=Html::a('<i class="fa fa-plus" aria-hidden="true"></i> Создать бизнес', '#', [
                'class' => 'btn btn-md btn-outline-success create-busines pull-right'
            ]);?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <?php Pjax::begin(['enablePushState' => false,'timeout' => 10000, 'id' => 'pjax-network-list'])?>
            <?=GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterPosition' => false,
                    'columns' => $grid, 
                    'options' => ['class' => 'table-responsive'],
                    'tableOptions' => ['class' => 'table table-bordered table-striped dataTable', 'role' => 'grid'],
                    'bordered' => false,
                    'striped' => true,
                    'summary' => false,
                    'condensed' => false,
                    'responsive' => false,
                    'hover' => false,
                       'resizableColumns'=>false,

            ]);
            ?> 
            <?php Pjax::end(); ?> 
    </div>
</div>