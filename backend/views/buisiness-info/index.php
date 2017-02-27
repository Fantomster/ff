<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\BusinessInfoSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Организации, одобренные для f-market';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="buisiness-info-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]);  ?>

    <?php Pjax::begin(); ?>    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'organization_id',
            [
                'attribute' => 'org_type_id',
                'value' => 'organization.type.name',
                'label' => 'Тип',
                'filter' => common\models\OrganizationType::getList(),
            ],
            [
                'format' => 'raw',
                'attribute' => 'org_name',
                'value' => function ($data) {
                    return Html::a($data['organization']['name'], ['organization/view', 'id' => $data['organization_id']]);
                },
                        'label' => 'Название организации',
                    ],
                    [
                        'attribute' => 'created_at',
                        'label' => 'Дата создания',
                        'value' => function ($data) {
                            return Yii::$app->formatter->asTime($data->created_at, "php:j M Y, H:i:s");
                        }
                    ],
                    [
                        'attribute' => 'updated_at',
                        'label' => 'Последнее изменение',
                        'value' => function ($data) {
                            return Yii::$app->formatter->asTime($data->updated_at, "php:j M Y, H:i:s");
                        }
                    ],
                    ['class' => 'yii\grid\ActionColumn'],
                ],
            ]);
            ?>
            <?php Pjax::end(); ?></div>
