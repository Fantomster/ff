<?php

use yii\helpers\Html;
//use yii\grid\GridView;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use kartik\export\ExportMenu;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\OrganizationSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Доступы R-keeper White Server';
$this->params['breadcrumbs'][] = $this->title;

$gridColumns = [
            'id',
            'org',
            'fd',
            'td',
    'status_id',
    'object_id',
];

/*
$gridColumns = [
    'id',
    [
        'attribute' => 'type_id',
        'value' => 'type.name',
        'label' => 'Тип',
        'filter' => common\models\OrganizationType::getList(),
    ],
    [
        'format' => 'raw',
        'attribute' => 'name',
        'value' => function ($data) {
            return Html::a($data['name'], ['organization/view', 'id' => $data['id']]);
        },
    ],
    'white_list',
    'partnership',
    'locality',
//    'address',
//    'zip_code',
    'phone',
    'email:email',
    [
        'attribute' => 'place_id',
        'label' => 'GEO',
        'format' => 'raw',
        'value' => function ($data) {
            if(empty($data->place_id)){
            return  Html::a('<span class="text-danger">Добавить адрес</span>', ['update', 'id' => $data->id]);
            }else{
            return  Html::a('<span class="text-success">Актуализирован</span>', ['update', 'id' => $data->id]);
            }
        }
    ],
    [
        'attribute' => 'place_id',
        'label' => 'У франшизы',
        'format' => 'raw',
        'value' => function ($data) {
            if(\common\models\FranchiseeAssociate::find()->where(['organization_id'=>$data->id])->exists()){
              return '<span class="text-success">Да</span>';   
            }
            return '';
        }
    ],
//    'website',
                // 'created_at',
                // 'updated_at',
                // 'step',
        ];
    */
        ?>

        <div class="organization-index">

            <h1><?= Html::encode($this->title) ?></h1>
            <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
            
            <?php Pjax::begin(['enablePushState' => true, 'id' => 'organizationList', 'timeout' => 5000]); ?>    
            <div class="catalog-index">
            <div class="box-header with-border">
              <div class="box-title pull-left">
                 <?= Html::a('<i class="fa fa-sign-in"></i> Отправить запрос', ['check'],['class'=>'btn btn-md fk-button']) ?>
              </div>
            </div>
            </div>  
            <div class="catalog-index">
            <div class="box-header with-border">
              <div class="box-title pull-right">
            <?php
            echo ExportMenu::widget([
                'dataProvider' => $dataProvider,
                'columns' => $gridColumns,
                'target' => ExportMenu::TARGET_SELF,
                'exportConfig' => [
                    ExportMenu::FORMAT_PDF => false,
                    ExportMenu::FORMAT_EXCEL_X => false,
                ],
            ]);
            ?>
            </div>
            </div>
            </div> 
            <?=
            GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => $gridColumns,
            ]);
            ?>
            <?php Pjax::end(); ?></div>
