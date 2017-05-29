<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\FranchiseeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Franchisees';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="franchisee-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Franchisee', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
<?php Pjax::begin(); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'id',
            [
                'attribute' => 'type_id',
                'value' => 'type.name',
                'label' => 'Тип',
                'filter' => common\models\FranchiseType::getList(),
            ],
            [
                'format' => 'raw',
                'attribute' => 'Регионы франшизы (GEO)',
                'value' => function ($data) {
                    if(\common\models\FranchiseeGeo::find()->where(['franchisee_id'=>$data['id']])->exists()){
                    return Html::a('Изменить', ['franchisee/geo', 'id' => $data['id']],['data-pjax'=>0, 'class'=>'text-success']);
                    }else{
                    return Html::a('Указать', ['franchisee/geo', 'id' => $data['id']],['data-pjax'=>0, 'class'=>'text-danger']);   
                    }
                },
            ],
            'signed',         
            [
                'format' => 'raw',
                'attribute' => 'Клиентов',
                'value' => function($data) {
                       return $data->getFranchiseeAssociates()->count();
              }
            ],
            'legal_entity',
            'legal_address',
            'legal_email:email',
            // 'inn',
            // 'kpp',
            // 'ogrn',
            // 'bank_name',
            // 'bik',
            // 'phone',
            // 'correspondent_account',
            // 'checking_account',
            // 'info:ntext',
            // 'created_at',
            // 'updated_at',
            
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
<?php Pjax::end(); ?></div>
