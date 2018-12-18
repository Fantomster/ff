<?php

use yii\widgets\Breadcrumbs;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use yii\web\View;
use yii\widgets\ListView;
use kartik\grid\GridView;
use kartik\editable\Editable;
use api\common\models\RkAccess;

?>


<style>
    .bg-default {
        background: #555
    }

    p {
        margin: 0;
    }

    #map {
        width: 100%;
        height: 200px;
    }
</style>
<section class="content-header">
    <h1>
        <i class="fa fa-upload"></i> Интеграция с R-keeper SH (White Server)
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links'   => [
            [
                'label' => 'Интеграция',
                'url'   => ['/clientintegr'],
            ],
            'Интеграция с R-keeper WS',
        ],
    ])
    ?>
</section>
<section class="content-header">
    <?= $this->render('/default/_menu.php'); ?>
    ПРОДУКТЫ Store House

</section>
<section class="content">
    <div class="catalog-index">

        <div class="box box-info">
            <div class="box-header with-border">
                <div class="panel-body">
                    <div class="box-body table-responsive no-padding">
                        <?=
                        GridView::widget([
                            'dataProvider'     => $dataProvider,
                            'pjax'             => false, // pjax is set to always true for this demo
                            //    'pjaxSettings' => ['options' => ['id' => 'kv-unique-id-1'], 'loadingCssClass' => false],
                            'filterPosition'   => false,
                            'columns'          => [
                                'group_name',
                                'rid',
                                'denom',
                                'unitname',
                                'updated_at',
                            ],
                            /* 'rowOptions' => function ($data, $key, $index, $grid) {
                              return ['id' => $data['id'], 'onclick' => "console.log($(this).find(a).first())"];
                              }, */
                            'options'          => ['class' => 'table-responsive'],
                            'tableOptions'     => ['class' => 'table table-bordered table-striped dataTable', 'role' => 'grid'],
                            'formatter'        => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
                            'bordered'         => false,
                            'striped'          => true,
                            'condensed'        => false,
                            'responsive'       => false,
                            'hover'            => true,
                            'resizableColumns' => false,
                            'export'           => [
                                'fontAwesome' => true,
                            ],
                        ]);
                        ?>
                        <?= Html::a('Вернуться',
                            ['/clientintegr/rkws/default'],
                            ['class' => 'btn btn-success btn-export']);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


