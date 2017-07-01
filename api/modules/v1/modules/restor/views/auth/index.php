<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use api\common\models\RkSession;
use api\common\models\RkSessionSearch;

$this->params['breadcrumbs'][] = $this->title;

echo $this->render('/default/_menu.php');

/* @var $this yii\web\View */
/* @var $searchModel api\common\models\RkAccessSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */


?>
<div class="rk-access-index">
    
    <h2>Авторизация</h2>
<?php
    echo Html::a('Status', ['index'], ['class'=>'btn bg-olive']);
    echo "&nbsp; &nbsp; &nbsp; ";
    echo Html::a('Send auth', ['sendlogin'], ['class'=>'btn bg-olive']);
    echo "&nbsp; &nbsp; &nbsp; ";
    echo Html::a('Get goods', ['srequest/index'], ['class'=>'btn bg-olive']);
    echo "&nbsp; &nbsp; &nbsp; ";
 ?>   
    
<?php 

Pjax::begin();  ?>  

  <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
            'attribute' => 'id',
            'format' => 'raw',    
            'contentOptions' => ['style' => 'max-width: 40px;'],     
            ],
            [
            'attribute' => 'fid',
            'format' => 'raw',                  
            'contentOptions' => ['style' => 'max-width: 40px;'],     
            ],
            [
            'attribute' => 'acc',
            'format' => 'raw',                  
            'contentOptions' => ['style' => 'max-width: 40px;'],     
            ],
            [
            'attribute' => 'fd',
            'format' => 'raw',                  
            'contentOptions' => ['style' => 'max-width: 40px;'],     
            ],
            [
            'attribute' => 'td',
            'format' => 'raw',                  
            'contentOptions' => ['style' => 'max-width: 40px;'],     
            ],
            [
            'attribute' => 'ver',
            'format' => 'raw',                  
            'contentOptions' => ['style' => 'max-width: 40px;'],     
            ],
            [
            'attribute' => 'status',
            'format' => 'raw',                  
            'contentOptions' => ['style' => 'max-width: 40px;'],     
            ],
            [
            'attribute' => 'extime',
            'format' => 'raw',                  
            'contentOptions' => ['style' => 'max-width: 40px;'],     
            ],
      
         /*   
            [
            'attribute' => 'org',
            'format' => 'text',  
            'label' => 'Организация',
        //    'value' => ''    
            'content'=>  function($data){
                         return $data->getOrganizationName();
                        },
          //  'filter' => Category::getParentsList()    
                
            ],
            [
            'attribute' => 'login',
            'format' => 'raw',    
            'contentOptions' => ['style' => 'max-width: 40px;'],     
            ],
         
         //   'password',
         //   'token',
         //   'lic:ntext',
            [
                'format' => 'raw',
                'attribute' => 'fd',
                'value' => function($data) {
                           $date = Yii::$app->formatter->asDatetime($data->fd, "php:j M Y");
                           return '<i class="fa fa-fw fa-calendar""></i> ' . $date;
                           },
                'label' => 'C',
            ],
            [
                'format' => 'raw',
                'attribute' => 'fd',
                'value' => function($data) {
                           $date = Yii::$app->formatter->asDatetime($data->td, "php:j M Y");
                           return '<i class="fa fa-fw fa-calendar""></i> ' . $date;
                           },
                'label' => 'По',
            ],
            [
                'attribute' => 'ver',
                'label' => 'Версия',
            ],   
            [
                'attribute' => 'locked',
                'format' =>'raw',
                'value' => function($data) {
                           return RkAccess::getStatusArray()[$data->locked]; 
                           },
                
            ],                      
            
         //   'usereq',
         //  'comment',

            */
            
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); 

 ?>
 
<?php Pjax::end(); ?></div>
