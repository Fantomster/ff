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
$org = User::findOne(Yii::$app->user->id)->organization_id;
$selectedStore = ArrayHelper::map(\api\common\models\iiko\iikoSelectedStore::find()->with('iikoStore')->where(['organization_id' => $org])->all(), 'iikoStore.id', 'iikoStore.denom');
if (!$selectedStore || count($selectedStore) == 0) {
    $selectedStore = ArrayHelper::map(\api\common\models\iiko\iikoStore::find()->where(['org_id' => $org, 'is_active' => 1])->all(), 'id', 'denom');
}
?>

<div class="dict-agent-form">

    <?php $agentModel = \api\common\models\iiko\iikoAgent::findOne(['org_id' => $org, 'uuid' => $model->agent_uuid]); ?>
    <?php $data = ($agentModel) ? [$agentModel->uuid => $agentModel->denom] : []; ?>

    <?php $form = ActiveForm::begin(); ?>

    <?php echo $form->errorSummary($model); ?>

    <?php echo $form->field($model, 'order_id')->textInput(['maxlength' => true, 'disabled' => 'disabled']) ?>

    <?php echo $form->field($model, 'text_code')->textInput(['maxlength' => true]) ?>

    <?php echo $form->field($model, 'num_code')->textInput(['maxlength' => true]) ?>

    <?php echo $form->field($model, 'agent_uuid')->widget(\kartik\select2\Select2::classname(), [
        'data' => \api\common\models\iiko\iikoAgent::getAgents($org),
        'pluginOptions' => [
            'allowClear' => true],
        'id' => 'orgFilter'
    ]);
    ?>

    <?php echo $form->field($model, 'store_id')->dropDownList($selectedStore) ?>
    <?php

    if (!$model->doc_date) {
        $model->doc_date = date('d.m.Y', time());
    } else {
        $rdate = date('d.m.Y', strtotime($model->doc_date));
        $model->doc_date = $rdate;
    }
    ?>
    <?= $form->field($model, 'doc_date')->label('Дата документа')->
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
            Url::previous(),
            ['class' => 'btn btn-success btn-export']);
        ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

