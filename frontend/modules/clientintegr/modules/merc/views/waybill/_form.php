<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\date\DatePicker;
use yii\helpers\ArrayHelper;
use kartik\tree\TreeViewInput;
use yii\bootstrap\Dropdown;
use common\models\User;
use kartik\widgets\Select2;
use yii\helpers\Url;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $model \api\common\models\iiko\iikoWaybill */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="dict-agent-form">

    <?php $org = User::findOne(Yii::$app->user->id)->organization_id;?>
    <?php $agentModel = \api\common\models\iiko\iikoAgent::findOne(['org_id' => $org, 'uuid' => $model->agent_uuid]); ?>
    <?php $data = ($agentModel) ? [$agentModel->uuid => $agentModel->denom] : []; ?>

    <?php if (empty($model->store_id)) $model->store_id = 1; ?>

    <?php $form = ActiveForm::begin(); ?>

    <?php echo $form->errorSummary($model); ?>

    <?php echo $form->field($model, 'order_id')->textInput(['maxlength' => true, 'disabled' => 'disabled']) ?>

    <?php echo $form->field($model, 'text_code')->textInput(['maxlength' => true]) ?>

    <?php echo $form->field($model, 'num_code')->textInput(['maxlength' => true]) ?>

    <?php echo $form->field($model, 'agent_uuid')->widget(Select2::classname(), [
        'data' => $data,
        'options' => ['placeholder' => 'Выберите контрагента...'],
        'pluginOptions' => [
            'minimumInputLength' => 2,
            'ajax' => [
                'url' => Url::toRoute('waybill/auto-complete-agent'),
                'dataType' => 'json',
                'data' => new JsExpression('function(params) { return {term:params.term, org:' . $org . '}; }')
            ],
            'allowClear' => true,
        ],
        'pluginEvents' => [
            "select2:select" => "function() {
                if($(this).val() == 0) {
                    $('#contract-modal').modal('show');
                } else {
                    var form = jQuery('#add');
                    jQuery.ajax({
                        url: form.attr('action'),
                        type: 'POST',
                        dataType: 'html',
                        data: form.serialize(),
                        success: function(response) {
                            $.pjax.reload({container:'#request_pjax', timeout: 16000});
                        },
                        error: function(response) {
                            console.log('server error');
                        }
                    });
                }
            }",
        ]
    ]);

    ?>

    <?php echo $form->field($model, 'store_id')->dropDownList(ArrayHelper::map(\api\common\models\iiko\iikoStore::find()->where(['org_id' => $org])->all(), 'id', 'denom')) ?>
    <?php

    if (!$model->doc_date) {
        $model->doc_date = date('d.m.Y', time());
    } else {
        $rdate = date('d.m.Y', strtotime($model->doc_date));
        $model->doc_date = $rdate;
    }
    ?>
    <?= $form->field($model, 'doc_date')->label('Дата Документа')->
    widget(DatePicker::classname(), [
        'type' => DatePicker::TYPE_COMPONENT_APPEND,
        'convertFormat' => true,
        'layout' => '{picker}{input}',
        'pluginOptions' => [
            'autoclose' => true,
            'format' => 'dd.MM.yyyy',
            'todayHighlight' => false,
        ],
    ]);
    ?>

    <?php echo $form->field($model, 'note')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?php echo Html::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        <?= Html::a('Вернуться',
            ['index'],
            ['class' => 'btn btn-success btn-export']);
        ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

