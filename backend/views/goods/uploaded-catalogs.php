<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

$this->title = 'Каталоги, загруженные ресторанами';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="uploaded-catalogs-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php Pjax::begin(); ?>    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a($data->client->name, ['organization/view', 'id' => $data->rest_org_id]);
                },
                'label' => 'Ресторан',
            ],
            [
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a($data->vendor->name, ['organization/view', 'id' => $data->supp_org_id]);
                },
                'label' => 'Поставщик',
            ],
            [
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a('Каталог', $data->getUploadUrl('uploaded_catalog'));
                },
                'label' => 'Загруженный каталог',
            ],
            [
                'format' => 'raw',
                'value' => function($data) {
                    return Html::a('Импортировать', ['goods/import-catalog', 'id' => $data->id]);
                }
            ],
        ],
    ]);
    ?>
    <?php Pjax::end(); ?></div>
