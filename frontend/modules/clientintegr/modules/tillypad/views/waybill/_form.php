<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\date\DatePicker;
use yii\helpers\ArrayHelper;
use common\models\User;
use yii\helpers\Url;
use api\common\models\iiko\iikoAgent;
use api\common\models\iiko\iikoSelectedStore;
use api\common\models\iiko\iikoStore;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model \api\common\models\iiko\iikoWaybill */
/* @var $form yii\bootstrap\ActiveForm */

$orgId = User::findOne(Yii::$app->user->id)->organization_id;

$selectedStoreInit = iikoSelectedStore::find()->with('iikoStore')->where(['organization_id' => $orgId])->all();
$selectedStore = ArrayHelper::map($selectedStoreInit, 'iikoStore.id', 'iikoStore.denom');

if (!$selectedStore || count($selectedStore) == 0) {
    $selectedStore = ArrayHelper::map(iikoStore::find()->where(['org_id' => $orgId, 'is_active' => 1])->all(), 'id', 'denom');
}
?>

<div class="dict-agent-form">

    <?php $agentModel = iikoAgent::findOne(['org_id' => $orgId, 'uuid' => $model->agent_uuid]); ?>
    <?php $data = ($agentModel) ? [$agentModel->uuid => $agentModel->denom] : []; ?>

    <?php $form = ActiveForm::begin(); ?>

    <?php echo $form->errorSummary($model); ?>

    <?php echo $form->field($model, 'order_id')->textInput(['maxlength' => true, 'disabled' => 'disabled']) ?>

    <?php echo $form->field($model, 'text_code')->textInput(['maxlength' => true]) ?>

    <?php echo $form->field($model, 'num_code')->textInput(['maxlength' => true]) ?>

    <?php

    $delays = iikoAgent::find()->select(['uuid', 'payment_delay'])->where(['org_id' => $orgId])->asArray()->all();
    $agentPaymentDelays = [];
    foreach ($delays as $k => $v) {
        $agentPaymentDelays[$v['uuid']] = ['data-payment-delay' => $v['payment_delay']];
    }

    ?>

    <?php echo $form->field($model, 'agent_uuid')->widget(Select2::class, [
        'data'          => iikoAgent::getAgents($orgId),
        'options'       => [
            'options' => $agentPaymentDelays,
        ],
        'pluginEvents'  => [
            "change" => "function() {
                var dateObj = Math.round(new Date().getTime() / 1000);
                var newDate = new Date((dateObj + $('option[value='+ $(this).val() +']').attr('data-payment-delay') * 24 * 60 * 60) * 1000);
                var y = newDate.getFullYear();
                var m = newDate.getMonth() + 1;
                if (m < 10) {m = '0' + m;}
                var d = newDate.getDate();
                if (d < 10) {d = '0' + d;}
                var res = d + '.' + m + '.' + y;
                $('#iikowaybill-payment_delay_date').val(res);
            }",
        ],
        'pluginOptions' => [
            'allowClear' => true],
        'id'            => 'orgFilter'
    ]);
    ?>

    <?php echo $form->field($model, 'store_id')->dropDownList($selectedStore) ?>
    <?php

    if (!$model->doc_date) {
        $model->doc_date = date('d.m.Y', time());
    } else {
        $model->doc_date = date('d.m.Y', strtotime($model->doc_date));
    }
    /*if (!$model->payment_delay_date) {
        $model->payment_delay_date = date('d.m.Y', time());
    } else {
        $model->payment_delay_date = date('d.m.Y', strtotime($model->payment_delay_date));
    }*/
    ?>

    <?= $form->field($model, 'doc_date')->label('Дата документа')->
    widget(DatePicker::class, [
        'type'          => DatePicker::TYPE_COMPONENT_APPEND,
        'convertFormat' => true,
        'layout'        => '{picker}{input}',
        'pluginOptions' => [
            'autoclose'      => true,
            'format'         => 'dd.MM.yyyy',
            'todayHighlight' => false,
        ],
    ]);
    ?>

    <?php /*
    echo $form->field($model, 'payment_delay_date')->label('Дата отсрочки платежа')->
    widget(DatePicker::class, [
        'type'          => DatePicker::TYPE_COMPONENT_APPEND,
        'convertFormat' => true,
        'layout'        => '{picker}{input}',
        'pluginOptions' => [
            'autoclose'      => true,
            'format'         => 'dd.MM.yyyy',
            'todayHighlight' => false,
        ],
    ]);*/
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