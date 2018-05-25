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
    <p>
        Файл json для frontend-api <a href="https://static.mixcart.ru/files/franchisee<?= (strpos(Yii::$app->request->hostName, 'test') || strpos(Yii::$app->request->hostName, 'ev') || strpos(Yii::$app->request->hostName, 'ackend')) ? '-dev' : '' ?>.json" target="_blank">https://static.mixcart.ru/files/franchisee<?= (strpos(Yii::$app->request->hostName, 'test') || strpos(Yii::$app->request->hostName, 'ev') || strpos(Yii::$app->request->hostName, 'ackend')) ? '-dev' : '' ?>.json</a>
    </p>
<?php Pjax::begin(); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'options' => [ 'style' => 'table-layout:fixed;' ],
        'columns' => [
            'id',
            [
                'attribute' => 'type_id',
                'value' => 'type.name',
                'label' => 'Тип',
                'filter' => common\models\FranchiseType::getList(),
                'contentOptions' => ['style' => 'width:20px; white-space: normal;'],
            ],
            [
                'format' => 'raw',
                'label' => 'Регионы франшизы (GEO)',
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
                'label' => 'Клиентов',
                'value' => function($data) {
                       $c_all = \common\models\FranchiseeAssociate::find()->joinWith('organization')->where([
                               'franchisee_id'=>$data->id])->count();

                       $c_client = \common\models\FranchiseeAssociate::find()->joinWith('organization')->where([
                               'franchisee_id'=>$data->id,
                               'organization.type_id'=>1,
                               ])->count();
                       $c_client_reg = \common\models\FranchiseeAssociate::find()->joinWith('organization')->where([
                               'franchisee_id'=>$data->id,
                               'self_registered'=>0,
                               'organization.type_id'=>1,
                               ])->count();
                       $c_client_self_reg = \common\models\FranchiseeAssociate::find()->joinWith('organization')->where([
                               'franchisee_id'=>$data->id,
                               'self_registered'=>\common\models\FranchiseeAssociate::SELF_REGISTERED,
                               'organization.type_id'=>1,
                               ])->count();
                       $c_vendor = \common\models\FranchiseeAssociate::find()->joinWith('organization')->where([
                               'franchisee_id'=>$data->id,
                               'organization.type_id'=>2,
                               ])->count();
                       $c_vendor_reg = \common\models\FranchiseeAssociate::find()->joinWith('organization')->where([
                               'franchisee_id'=>$data->id,
                               'self_registered'=>0,
                               'organization.type_id'=>2,
                               ])->count();
                       $c_vendor_self_reg = \common\models\FranchiseeAssociate::find()->joinWith('organization')->where([
                               'franchisee_id'=>$data->id,
                               'self_registered'=>\common\models\FranchiseeAssociate::SELF_REGISTERED,
                               'organization.type_id'=>2,
                               ])->count();
                        return "<b>$c_all</b> <br>"
                                . "Рестораны: <b>$c_client</b> ($c_client_reg/$c_client_self_reg) <br>"
                                . "Поставщики: <b>$c_vendor</b> ($c_vendor_reg/$c_vendor_self_reg)";
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
