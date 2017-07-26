 <?php
 
 use kartik\grid\GridView;
 use yii\data\ActiveDataProvider;
 use common\models\User;

 ?>
Приходная Накладная:<br><br>

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
                                                'doc_date',
                                                'note',      
                                                                                            [
                                                'class' => 'yii\grid\ActionColumn',
                                                'contentOptions'=>['style'=>'width: 6%;'],
                                                'template'=>'{view}&nbsp;{update}&nbsp;{map}',
                                                    'visibleButtons' => [
     
                                                        'update' => function ($model, $key, $index) {
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
                                                        $customurl=Yii::$app->getUrlManager()->createUrl(['clientintegr\rkws\waybill\update', 'id'=>$model->id]);
                                                        return \yii\helpers\Html::a( '<i class="fa fa-pencil" aria-hidden="true"></i>', $customurl,
                                                                     ['title' => Yii::t('backend', 'Update'), 'data-pjax'=>"0"]);
                                                           },
                                                       'map' =>  function ($url, $model) {
                                                      //  if (Helper::checkRoute('/prequest/default/update', ['id' => $model->id])) {
                                                        $customurl=Yii::$app->getUrlManager()->createUrl(['map', 'id'=>$model->id]);
                                                        return \yii\helpers\Html::a( '<i class="fa fa-chain" aria-hidden="true"></i>', $customurl,
                                                                     ['title' => Yii::t('backend', 'Map'), 'data-pjax'=>"0"]);
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

