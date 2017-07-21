<?php

use yii\widgets\Breadcrumbs;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use yii\web\View;
use yii\widgets\ListView;
use kartik\grid\GridView;
use kartik\editable\Editable;
use api\common\models\RkAccess;



?>


<style>
.bg-default{background:#555} p{margin: 0;} #map{width:100%;height:200px;}
</style>
<section class="content-header">
    <h1>
        <i class="fa fa-upload"></i> Интеграция с R-keeper STORE HOUSE White Server 
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            [
                'label' => 'Интеграция',
                'url' => ['/clientintegr/default'],
            ],
            'Интеграция с R-keeper White Server',
        ],
    ])
    ?>
</section>
<section class="content-header">
    <?= $this->render('/default/_menu.php'); ?>

</section>

<section class="content-header">
    
	<div class="box box-info">            
            <div class="box-header with-border">
                            <div class="panel-body">
                                <div class="box-body table-responsive no-padding">
                                    
                                <p> Состояние лицензии: Активна.
                                </p>    
                            
                          
                                </div>
                            </div>
            </div>
        </div>    
                                
</section>
<section class="content-header">
    

                                    
    	<div class="box box-info">            
            <div class="box-header with-border">
                            <div class="panel-body">
                                <div class="box-body table-responsive no-padding">
                                    <?=
                                    GridView::widget([
                                        'dataProvider' => $dataProvider,
                                        'pjax' => true, // pjax is set to always true for this demo
                                    //    'pjaxSettings' => ['options' => ['id' => 'kv-unique-id-1'], 'loadingCssClass' => false],
                                        'filterPosition' => false,
                                        'columns' => [
                                            'id',
                                            [
                                                'attribute'=>'dictype_id',
                                                'value'=>function ($model) {
                                                            return $model->dictype->denom;
                                                         },
                                                'format'=>'raw',
                                                'contentOptions'=>['style'=>'width: 10%;']        
                                            ],
                                     
                                            'created_at',
                                            'updated_at',
                                            'obj_count',
                                            'obj_mapcount',
                                            [
                                                'attribute'=>'dicstatus_id',
                                                'value'=>function ($model) {
                                                            return $model->dicstatus->denom;
                                                         },
                                                'format'=>'raw',
                                                'contentOptions'=>['style'=>'width: 10%;']        
                                            ],
                                            // **********
                                            [
                                                'class' => 'yii\grid\ActionColumn',
                                                'contentOptions'=>['style'=>'width: 6%;'],
                                                'template'=>'{view}&nbsp;{update}&nbsp;{getws}&nbsp;{map}',
                                                    'visibleButtons' => [
     
                                                        'update' => function ($model, $key, $index) {
                                                        // return (($model->status_id > 2 && $model->status_id != 8 && $model->status_id !=5) && Yii::$app->user->can('Rcontroller') || (Yii::$app->user->can('Requester') && (($model->status_id === 2) || ($model->status_id === 4))) ) ? true : false;
                                                        return true;     
                                                        },           
                                                        'getws' => function ($model, $key, $index) {
                                                        // return (($model->status_id > 2 && $model->status_id != 8 && $model->status_id !=5) && Yii::$app->user->can('Rcontroller') || (Yii::$app->user->can('Requester') && (($model->status_id === 2) || ($model->status_id === 4))) ) ? true : false;
                                                        return true;     
                                                        },                   
                                                        'map' => function ($model, $key, $index) {
                                                        // return (($model->status_id > 2 && $model->status_id != 8 && $model->status_id !=5) && Yii::$app->user->can('Rcontroller') || (Yii::$app->user->can('Requester') && (($model->status_id === 2) || ($model->status_id === 4))) ) ? true : false;
                                                        return true;     
                                                        },                           
                                                    ],
                
                                                'buttons'=>[
                
                                                        'update' =>  function ($url, $model) {
                                                      //  if (Helper::checkRoute('/prequest/default/update', ['id' => $model->id])) {
                                                        $customurl=Yii::$app->getUrlManager()->createUrl(['clientintegr\rkws\\'.$model->dictype->contr.'\index', 'id'=>$model->id]);
                                                        return \yii\helpers\Html::a( '<i class="fa fa-pencil" aria-hidden="true"></i>', $customurl,
                                                                     ['title' => Yii::t('backend', 'Update'), 'data-pjax'=>"0"]);
                                                           },
                                                        'getws' =>  function ($url, $model) {
                                                        $customurl=Yii::$app->getUrlManager()->createUrl(['clientintegr\rkws\\'.$model->dictype->contr.'\getws', 'id'=>$model->id]);
                                                        return \yii\helpers\Html::a( '<i class="fa fa-download" aria-hidden="true"></i>', $customurl,
                                                                     ['title' => Yii::t('backend', 'Update'), 'data-pjax'=>"0"]);
                                                           },     
                                                        'map' =>  function ($url, $model) {
                                                        return \yii\helpers\Html::a( '<i class="fa fa-chain" aria-hidden="true"></i>', $customurl,
                                                                     ['title' => Yii::t('backend', 'Update'), 'data-pjax'=>"0"]);
                                                           },           
                                                  
                                                           ]                               
                                                                   
                                            ] 
                                            // **********                     
                                        ],
                                        /* 'rowOptions' => function ($data, $key, $index, $grid) {
                                          return ['id' => $data['id'], 'onclick' => "console.log($(this).find(a).first())"];
                                          }, */
                                        'options' => ['class' => 'table-responsive'],
                                        'tableOptions' => ['class' => 'table table-bordered table-striped dataTable', 'role' => 'grid'],
                                        'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
                                        'bordered' => false,
                                        'striped' => true,
                                        'condensed' => false,
                                        'responsive' => false,
                                        'hover' => true,
                                        'resizableColumns' => false,
                                        'export' => [
                                            'fontAwesome' => true,
                                        ],
                                    ]);
                                    ?> 
                                </div>
                            </div>    
                </div>
            </div>   
                            
                          

                                
</section>

