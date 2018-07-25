<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use kartik\editable\Editable;
use kartik\daterange\DateRangePicker;
;

$this->title = 'Заказы с прикрепленными файлами';

$columns = [
    [
        'format' => 'raw',
        'attribute' => 'order_id',
        'value' => function ($data) {
            return Html::a($data->order_id, ['order/edit', 'id' => $data->order_id], ['data-pjax' => 0]);
        },
        'label' => 'ID заказа',
        'group' => true,
    ],
    'file',
    [
        'format' => 'raw',
        'filter' => DateRangePicker::widget([
            'model' => $searchModel,
            'attribute' => 'created_at_range',
            'options' => [
                'id' => 'dateRangeFilter',
            ],
            'pluginOptions' => [
                'format' => 'd-m-Y',
                'autoUpdateInput' => false
            ],
            'pluginEvents' => [
                "cancel.daterangepicker" => "function(ev, picker) { 
                    //alert(1);
                    $('#dateRangeFilter').val('').trigger('change');
                }"],
            'hideInput' => true,
            'pjaxContainerId' => 'orderList',
        ]),
        'attribute' => 'created_at',
        'label' => 'Прикреплено',
        'value' => function ($data) {
            return Yii::$app->formatter->asTime($data->created_at, "php:j M Y, H:i:s");
        }
    ],
    [
        'format' => 'raw',
        'filter' => common\models\User::getMixManagersList(),
        'attribute' => 'assigned_to',
        'label' => 'Назначен (кому)',
        'value' => function ($data) {
            $model = isset($data->assignment) ? $data->assignment : new \common\models\OrderAssignment();
            $display = [];
            if (isset($model->assigned_to)) {
                $display = [$model->assigned_to => Html::a($data->assignment->assignedTo->profile->full_name, ['client/view', 'id' => $model->assigned_to], ['data-pjax' => 0])];
            }
            return Editable::widget([
                        'model' => $model,
                        'attribute' => 'assigned_to',
                        'asPopover' => true,
                        //'header' => 'Province',
                        'format' => Editable::FORMAT_BUTTON,
                        'inputType' => Editable::INPUT_DROPDOWN_LIST,
                        'data' => common\models\User::getMixManagersList(), // any list of values
                        'displayValueConfig' => $display,
                        'options' => [
                            'id' => 'edit' . $data->order_id,
                            'class' => 'form-control',
                            'prompt' => 'Возложить ответственность...',
                        ],
                        'formOptions' => [
                            'action' => Url::to(['/order/assign', 'id' => $data->order_id]),
                        ],
                        'pluginEvents' => [
                            "editableSuccess" => "function(event, val, form, data) {
                                $.pjax.reload({container: '#orderList', timeout:30000}); 
                            }",
                        ],
                        'editableValueOptions' => ['class' => 'text-danger'],
                        'pjaxContainerId' => 'orderList',
            ]); //isset($data->assignment) ? $data->assignment->assigned_to : null;
        },
        'group' => true,
        'subGroupOf' => 0,
    ],
    [
        'format' => 'raw',
        'attribute' => 'is_processed',
        'label' => 'Обработан',
        'value' => function ($data) {
            return isset($data->assignment) ? $data->assignment->is_processed : null;
        },
        'group' => true,
        'subGroupOf' => 0,
    ],
];

$this->registerCss('
        td{vertical-align:middle !important;}
        ');
?>

<?php Pjax::begin(['enablePushState' => false, 'id' => 'orderList', 'timeout' => 5000]); ?> 
<?=

kartik\grid\GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'options' => ['style' => 'table-layout:fixed;'],
    'columns' => $columns,
    //'pjax' => true,
    'id' => 'ordersGrid',
])
?>
<?php Pjax::end(); ?>