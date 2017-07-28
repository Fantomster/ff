 <?php
 
 use kartik\grid\GridView;
 use yii\data\ActiveDataProvider;
 use common\models\User;
 use yii\helpers\Html;

 ?>
Приходная Накладная:<br><br>

<?php 
    
    // var_dump($model);   

    if(empty($model)) {
        
         echo Html::a('Создать накладную', ['create','order_id'=>$order_id], ['class'=>'btn btn-md fk-button']);   
    } else {

?>

                                    <?=
                                    GridView::widget([
                                        'dataProvider' => new ActiveDataProvider([
                                        'query' => $model,
                                        'sort' => false,
                                                                                ]),
                                        'layout' => '{items}',
                                        'pjax' => true, // pjax is set to always true for this demo
                                    //    'pjaxSettings' => ['options' => ['id' => 'kv-unique-id-1'], 'loadingCssClass' => false],
                                        'filterPosition' => false,
                                        'columns' => [
                                                'id',
                                                'order_id',
                                                [
                                                    'attribute' => 'corr_rid',
                                                    'value'=>function ($model) {
                                                              return $model->corr->denom;

                                                                 },
                                                                    
                                                ],  
                                                [
                                                    'attribute' => 'store_rid',
                                                    'value'=>function ($model) {
                                                              return $model->store->denom;

                                                                 },
                                                                    
                                                ],  
                                                [
                                                    'attribute' =>'doc_date',
                                                    'format' => 'date',
                                                    
                                                ],
                                                'note', 
                                                [
                                                'attribute'=>'readytoexport',
                                                'label' => 'К выгрузке',   
                                                'value'=>function ($model) {
                                                    return $model->readytoexport ? 'готова' : 'не готова';
                                                         },    
                                                ],      
                                                [
                                                'attribute'=>'status_id',
                                                'label' => 'Статус',   
                                                'value'=>function ($model) {
                                                    return $model->status->denom;
                                                         },    
                                                ],                   
                                                                                            [
                                                'class' => 'yii\grid\ActionColumn',
                                                'contentOptions'=>['style'=>'width: 6%;'],
                                                'template'=>'{update}&nbsp;{map}&nbsp;{export}',
                                                    'visibleButtons' => [
     
                                                        'update' => function ($model, $key, $index) {
                                                        // return (($model->status_id > 2 && $model->status_id != 8 && $model->status_id !=5) && Yii::$app->user->can('Rcontroller') || (Yii::$app->user->can('Requester') && (($model->status_id === 2) || ($model->status_id === 4))) ) ? true : false;
                                                        return true;     
                                                        },   
                                                        'map' => function ($model, $key, $index) {
                                                        // return (($model->status_id > 2 && $model->status_id != 8 && $model->status_id !=5) && Yii::$app->user->can('Rcontroller') || (Yii::$app->user->can('Requester') && (($model->status_id === 2) || ($model->status_id === 4))) ) ? true : false;
                                                        return true;     
                                                        },          
                                                        'export' => function ($model, $key, $index) {
                                                        return $model->readytoexport ? true : false;
                                                        return true;     
                                                        },                  
                          
                                                    ],
                
                                                'buttons'=>[
                
                                                        'update' =>  function ($url, $model) {
                                                      //  if (Helper::checkRoute('/prequest/default/update', ['id' => $model->id])) {
                                                        $customurl=Yii::$app->getUrlManager()->createUrl(['clientintegr\rkws\waybill\update', 'id'=>$model->id]);
                                                        return \yii\helpers\Html::a( '<i class="fa fa-pencil" aria-hidden="true"></i>', $customurl,
                                                                     ['title' => Yii::t('backend', 'Изменить шапку'), 'data-pjax'=>"0"]);
                                                           },
                                                       'map' =>  function ($url, $model) {
                                                      //  if (Helper::checkRoute('/prequest/default/update', ['id' => $model->id])) {
                                                        $customurl=Yii::$app->getUrlManager()->createUrl(['clientintegr\rkws\waybill\map', 'waybill_id'=>$model->id]);
                                                        return \yii\helpers\Html::a( '<i class="fa fa-chain" aria-hidden="true"></i>', $customurl,
                                                                     ['title' => Yii::t('backend', 'Сопоставить'), 'data-pjax'=>"0"]);
                                                           },           
                                                       'export' =>  function ($url, $model) {
                                                      //  if (Helper::checkRoute('/prequest/default/update', ['id' => $model->id])) {
                                                        $customurl=Yii::$app->getUrlManager()->createUrl(['clientintegr\rkws\waybill\sendws', 'waybill_id'=>$model->id]);
                                                        return \yii\helpers\Html::a( '<i class="fa fa-upload" aria-hidden="true"></i>', $customurl,
                                                                     ['title' => Yii::t('backend', 'Выгрузить'), 'data-pjax'=>"0"]);
                                                           },                                                      
                                                  
                                                           ]                               
                                                                   
                                            ]                          
                                                ],
                                        /* 'rowOptions' => function ($data, $key, $index, $grid) {
                                          return ['id' => $data['id'], 'onclick' => "console.log($(this).find(a).first())"];
                                          }, */
                                        'options' => ['class' => 'table-responsive'],
                                      //  'tableOptions' => ['class' => 'table table-bordered table-striped dataTable', 'role' => 'grid'],
                                        'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
                                        'bordered' => false,
                                        'striped' => true,
                                        'condensed' => true,
                                        'responsive' => false,
                                        'hover' => true,
                                        'resizableColumns' => false,
                                        'export' => [
                                            'fontAwesome' => true,
                                        ],
                                    ]);
                                    ?> 
    <?php } ?>

