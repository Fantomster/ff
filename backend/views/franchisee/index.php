<?php

use yii\helpers\Html;
use yii\grid\GridView;
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
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'signed',
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
