<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\web\View;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use kartik\depdrop\DepDrop;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\VatsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

kartik\select2\Select2Asset::register($this);
kartik\checkbox\KrajeeFlatBlueThemeAsset::register($this);
$this->title = Yii::t('app', 'Ставки налогов по странам');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="vats-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]);   ?>

    <div class="vats-country-button">
        <p>
            <?= Html::a('Добавить в список новую страну с перечнем ставок налогов', ['create'], ['class' => 'btn btn-success']) ?>
        </p>
        <br>
    </div>

    <?php Pjax::begin(); ?>    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel'  => $searchModel,
        'pjax'         => true, // pjax is set to always true for this demo
        'pjaxSettings' => ['options' => ['id' => 'kv-unique-id-1'], 'loadingCssClass' => false],
        'columns'      => [
            [
                'attribute' => 'country_name',
                'label'     => 'Название',
                'value'     => function ($data) {
                    return $data->country->name;
                }
            ],
            [
                'format'    => 'raw',
                'attribute' => 'vats',
                'label'     => 'Ставки налогов',
            ],
            [
                'class'    => 'yii\grid\ActionColumn',
                'template' => '{edit}',
                'buttons'  => [
                    'edit' => function ($url, $model) {
                        $customurl = Yii::$app->getUrlManager()->createUrl(['vats/update', 'id' => $model['id']]);
                        return \yii\helpers\Html::a('<span class="glyphicon glyphicon-pencil"></span>', $customurl,
                            ['title' => Yii::t('yii', 'Update'), 'data-pjax' => '0']);
                    },
                ],
            ],
        ],
    ]);
    ?>
    <?php Pjax::end(); ?></div>