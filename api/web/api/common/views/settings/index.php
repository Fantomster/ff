<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel api\common\models\RkAccessSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Rk Accesses';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="rk-access-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Rk Access', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
<?php Pjax::begin(); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'fid',
            'org',
            'login',
            'password',
            // 'token',
            // 'lic:ntext',
            // 'fd',
            // 'td',
            // 'ver',
            // 'locked',
            // 'usereq',
            // 'comment',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
<?php Pjax::end(); ?></div>
