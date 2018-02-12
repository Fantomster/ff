<?php

use yii\helpers\Html;
//use yii\grid\GridView;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use kartik\export\ExportMenu;
use kartik\grid\ActionColumn;
use yii\helpers\ArrayHelper;
use api\common\models\RkServicedata;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\OrganizationSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Доступы R-keeper White Server';

$this->params['breadcrumbs'][] = [
    'label' => 'Управление лицензиями',
    'url' => '/integration'
];

$this->params['breadcrumbs'][] = $this->title;

$gridColumns = [
    'id',
    'code',
    'name',
    'fd',
    'td',
   /* [
        'attribute' => 'org',
        'label' => 'Организация MixCart',
        'value' => function ($model) {
            if (isset($model))
                return $model->organization ? $model->organization->name : null;

        },

    ], */
    [
        'attribute' => 'last_active',
        'label' => 'Посл. Активность',
    ],
    [
        'attribute' => 'status_id',
        'value' => function ($model) {
            if ($model) return ($model->status_id == 2) ? 'Активно' : 'Неактивно';

        },
    ],
    [
        'class'=>'kartik\grid\ExpandRowColumn',
        'width'=>'50px',
        'value'=>function ($model, $key, $index, $column) {
            return GridView::ROW_COLLAPSED;
        },
        'detail'=>function ($model, $key, $index, $column) {
          $wmodel = RkServicedata::find()->andWhere('service_id = :service_id',[':service_id'=> $model->id])->one();

          if ($wmodel) {
                $wmodel = RkServicedata::find()->andWhere('service_id = :service_id',[':service_id'=> $model->id]);
          } else {
                $wmodel = null;
          }
             $service_id = $model->id;

            return Yii::$app->controller->renderPartial('_expand-row-details', ['model'=>$wmodel,'service_id'=>$service_id]);
        },
        'headerOptions'=>['class'=>'kartik-sheet-style'],
        'expandOneOnly'=>true,
    ],
   // 'org',
    /*
    [
        'class' => 'yii\grid\ActionColumn',
        'template' => '{update}',
     //   'visibleButtons' => [
     //       'update' => function ($model, $key, $index) {
     //           // return (($model->status_id > 2 && $model->status_id != 8 && $model->status_id !=5) && Yii::$app->user->can('Rcontroller') || (Yii::$app->user->can('Requester') && (($model->status_id === 2) || ($model->status_id === 4))) ) ? true : false;
     //           return true;
     //       },
     //   ],
        'buttons' => [
            'update' => function ($url, $model) {
                //  if (Helper::checkRoute('/prequest/default/update', ['id' => $model->id])) {
                $customurl = Yii::$app->getUrlManager()->createUrl(['rkws/update', 'id' => $model->id]);
                return \yii\helpers\Html::a('Изменить', $customurl, ['title' => 'Изменить', 'data-pjax' => "0"]);
            },
        ]
    ]
    */
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
<?php // echo $this->render('_search', ['model' => $searchModel]);  ?>

<?php Pjax::begin(['enablePushState' => true, 'id' => 'organizationList', 'timeout' => 5000]); ?>    
    <div class="catalog-index">
        <div class="box-header with-border">
            <div class="box-title pull-left">
<?= Html::a('<i class="fa fa-sign-in"></i> Обновить доступы', ['getws'], ['class' => 'btn btn-md fk-button']) ?>
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
    'batchSize' => 200,
    'timeout' => 0,
    'exportConfig' => [
        ExportMenu::FORMAT_PDF => false,
        ExportMenu::FORMAT_EXCEL => false,
        ExportMenu::FORMAT_EXCEL_X => [
            'label' => Yii::t('kvexport', 'Excel 2007+ (xlsx)'),
            'icon' => 'floppy-remove',
            'iconOptions' => ['class' => 'text-success'],
            'linkOptions' => [],
            'options' => ['title' => Yii::t('kvexport', 'Microsoft Excel 2007+ (xlsx)')],
            'alertMsg' => Yii::t('kvexport', 'The EXCEL 2007+ (xlsx) export file will be generated for download.'),
            'mime' => 'application/application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'extension' => 'xlsx',
            'writer' => 'Excel2007'
        ],
    ],
]);
?>
            </div>
        </div>
    </div> 
    <div class="catalog-index">
        <div class="box-header with-border">
            <div class="box-title pull-left">
                <?=
                GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'columns' => $gridColumns,
                ]);
                ?>
            </div>
        </div>
    </div>       
                <?php Pjax::end(); ?></div>
