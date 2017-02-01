<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\WhiteListSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Организации, одобренные для f-market';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="white-list-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

<?php Pjax::begin(); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'organization_id',
            [
                'format' => 'raw',
                'attribute' => 'org_name',
                'value' => function ($data) {
                    return Html::a($data['organization']['name'], ['organization/view', 'id' => $data['organization_id']]);
                },
                'label' => 'Название организации',
            ],
            'created_at',
            'updated_at',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
<?php Pjax::end(); ?></div>
