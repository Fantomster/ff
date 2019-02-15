<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $searchModel common\models\PaymentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Платежи';
$this->params['breadcrumbs'][] = $this->title;
$model = new \common\models\Payment();

\common\assets\SweetAlertAsset::register($this);

$organization = \yii\helpers\ArrayHelper::map(\common\models\Organization::find(['status' => 1])->asArray()->all(),'id','name');
$organization[0] = '---';
ksort($organization);

$types = \yii\helpers\ArrayHelper::map(\common\models\PaymentType::find()->asArray()->all(), 'type_id', 'title');
$types[0] = '---';
ksort($types);
?>
<div class="payment-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="show-payment-button" >
        <?=Html::tag('a','Добавить платеж',['class' => 'btn btn-success btn-panel-payment'])?>
        <br>
        <br>
    </div>

    <div class="well col-sm-12 payment-form" style="display: none;" >
        <?php $form = ActiveForm::begin(['enableClientValidation' => true, 'action' => ['create']]); ?>
        <div class="col-sm-3">
            <?= $form->field($model, 'date')->widget(\kartik\date\DatePicker::className(),[
                'pluginOptions' => [
                    'autoclose'=>true,
                    'format' => 'dd.mm.yyyy'
                ]
            ]) ?>
        </div>
        <div class="col-sm-3">
            <?= $form->field($model, 'total')->textInput() ?>
        </div>
        <div class="col-sm-3">
            <?= $form->field($model, 'receipt_number')->textInput() ?>
        </div>
        <div class="col-sm-3">
            <?= $form->field($model, 'type_payment')->dropDownList($types)?>
        </div>
        <div class="col-sm-12">
            <?= $form->field($model, 'organization_id')->widget(\kartik\select2\Select2::classname(), [
                'data' => $organization,
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ]);
            ?>
        </div>
        <div class="col-sm-12 text-right">
            <?=Html::submitButton('Сохранить',['class' => 'btn btn-success btn-submit-form', 'data-pjax' => 1])?>
            <?=Html::tag('a','Отмена',['class' => 'btn btn-danger payment-form-hide'])?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>

    <?= \kartik\grid\GridView::widget([
        'pjax' => true,
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'payment_id',
            [
                'attribute' => 'date',
                'filterType' => \kartik\grid\GridView::FILTER_DATE,
                'filterWidgetOptions' =>([
                    'model'=>$model,
                    'attribute'=>'date',
                    'pluginOptions'=>[
                        'autoclose'=>true,
                        'format'=>'dd.mm.yyyy',
                    ]
                ]),
                'value' => function($data) {
                    return date('d.m.Y', strtotime($data->date));
                }
            ],
            'total',
            'receipt_number',
            [
                'attribute' => 'organization.name',
                'filter' => Html::dropDownList(
                        'PaymentSearch[organization_id]',
                        Yii::$app->request->get('PaymentSearch')['organization_id'],
                        $organization,
                        [
                            'style' => 'width:200px',
                            'class' => 'form-control'
                        ]
                ),
            ],
            [
                'attribute' => 'payment.title',
                'filter' => Html::dropDownList(
                    'PaymentSearch[type_payment]',
                    Yii::$app->request->get('PaymentSearch')['type_payment'],
                    $types,
                    [
                        'style' => 'width:180px',
                        'class' => 'form-control'
                    ]
                ),
            ],
            [
                'class' => 'kartik\grid\ActionColumn',
                'template' => '{delete}',
                'options' => [
                    'data-pjax' => 1
                ]
            ]
        ],
    ]); ?>
</div>

<?php
$js = "
        $('.btn-panel-payment').click(function(){
            $('.payment-form').show();
            $('.btn-panel-payment').hide();
        });
        
        $('.payment-form-hide').click(function(){
            $('.payment-form').hide();
            $('.btn-panel-payment').show();
        });
        
        $('form').on('beforeSubmit', function(e) {
            var form = $(this);
            var formData = form.serialize();
            $.ajax({
                url: form.attr('action'),
                type: form.attr('method'),
                data: formData,
                success: function (data) {
                    if(data.success == true) {
                        swal({
                            type: 'success',
                            title: 'Готово',
                        });
                        $.pjax.reload({container:'#w1-pjax'});
                    } else {
                        swal({
                            type: 'error',
                            title: 'Ошибка при сохранении, проверьте данные.'
                        });
                    }                    
                },
                error: function () {
                    swal({
                        type: 'error',
                        title: 'Ошибка при сохранении, проверьте данные.'
                    });
                }
            });
        }).on('submit', function(e){
            e.preventDefault();
        });
    ";

$this->registerJs($js);
?>