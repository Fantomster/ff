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
use \yii\web\JsExpression;

$this->title = 'Интеграция с 1С Общепит';

?>
<section class="content-header">
    <h1>
        <i class="fa fa-upload"></i> <?=$this->title?>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            [
                'label' => 'Интеграция',
                'url' => ['/clientintegr'],
            ],
            $this->title
        ],
    ])
    ?>
</section>
<section class="content-header">
    <?= $this->render('/default/_menu.php'); ?>
    Контрагенты 1С
</section>
<section class="content">
    <div class="catalog-index">
        <div class="box box-info">
            <div class="box-header with-border">
                <div class="panel-body">
                    <div class="box-body table-responsive no-padding">
                        <?=
                        GridView::widget([
                            'dataProvider' => $dataProvider,
                            'pjax' => false,
                            'columns' => [
                                'id',
                                'cid',
                                'name',
                                [
                                    'class' => 'kartik\grid\EditableColumn',
                                    'attribute' => 'vendor_id',
                                    'value' => function ($model) {
                                        $vendor = $model->vendor;
                                        return isset($vendor) ? $vendor->name : null;
                                    },
                                    'label' => 'Поставщик MixCart',
                                    'vAlign' => 'middle',
                                    'width' => '210px',
                                    'refreshGrid' => true,
                                    'editableOptions' => [
                                        'asPopover' => true,
                                        'name' => 'vendor_id',
                                        'formOptions' => ['action' => ['agent-mapping']],
                                        'header' => 'Поставщик MixCart',
                                        'size' => 'md',
                                        'inputType' => \kartik\editable\Editable::INPUT_SELECT2,
                                        'options' => [
                                            'options' => ['placeholder' => 'Выберите поставщика из списка',
                                            ],
                                            'pluginOptions' => [
                                                'minimumInputLength' => 2,
                                                'ajax' => [
                                                    'url' => Url::toRoute(['agent-autocomplete']),
                                                    'dataType' => 'json',
                                                    'data' => new JsExpression('function(params) { return {term:params.term}; }')
                                                ],
                                                'allowClear' => true
                                            ],
                                        ]
                                    ]],
                                'inn_kpp',
                                'updated_at',
                            ],
                            'filterPosition' => false,
                            'options' => ['class' => 'table-responsive'],
                            'tableOptions' => ['class' => 'table table-bordered table-striped dataTable', 'role' => 'grid'],
                            'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
                            'bordered' => false,
                            'striped' => true,
                            'condensed' => false,
                            'responsive' => false,
                            'hover' => true,
                            'resizableColumns' => false,
                            'export' => [
                                'fontAwesome' => true,
                            ],
                        ]);
                        ?>
                        <?= Html::a('Вернуться', ['/clientintegr/odinsobsh/default'], ['class' => 'btn btn-success btn-export'])?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


