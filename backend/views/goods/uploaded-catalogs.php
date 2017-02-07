<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

$this->title = 'Каталоги загруженные ресторанами';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="uploaded-catalogs-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php Pjax::begin(); ?>    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'value' => 'client.name',
                'label' => 'Ресторан',
            ],
            [
                'value' => 'vendor.name',
                'label' => 'Поставщик',
            ],
            [
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a('Каталог', $data->getUploadUrl('uploaded_catalog'));
                },
                'label' => 'Загруженный каталог',
            ],
        ],
    ]);
    ?>
    <?php Pjax::end(); ?></div>
