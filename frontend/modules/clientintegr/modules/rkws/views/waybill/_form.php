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
use api\common\models\RkDicconst;

/* @var $this yii\web\View */
/* @var $model common\models\pdict\DictAgent */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="dict-agent-form">

    <?php $org = User::findOne(Yii::$app->user->id)->organization_id; // var_dump($org); ?>
    <?php $agentModel = \api\common\models\RkAgent::findOne(['acc' => $org, 'rid' => $model->corr_rid]); ?>
    <?php $data = ($agentModel) ? [$agentModel->rid => $agentModel->denom] : []; ?>
    <?php $autoNumber = (RkDicconst::findOne(['denom' => 'useAutoNumber'])->getPconstValue() != null) ?
        (RkDicconst::findOne(['denom' => 'useAutoNumber'])->getPconstValue()) : 0; ?>


    <?php if (empty($model->store_rid)) $model->store_rid = -1; ?>
    <?php if ($autoNumber) {
        $model->text_code = "mixcart";
        $model->num_code = $model->order_id;
    }

    ?>
    <?php $form = ActiveForm::begin(); ?>

    <?php echo $form->errorSummary($model); ?>

    <?php echo $form->field($model, 'order_id')->textInput(['maxlength' => true, 'disabled' => 'disabled']) ?>

    <?php echo $form->field($model, 'text_code')->textInput(['maxlength' => true]) ?>

    <?php echo $form->field($model, 'num_code')->textInput(['maxlength' => 10]) ?>

    <?php echo $form->field($model, 'corr_rid')->widget(\kartik\select2\Select2::classname(), [
        'data'          => \api\common\models\RkAgent::getAgents($org),
        'pluginOptions' => [
            'allowClear' => true],
        'id'            => 'orgFilter'
    ]);
    ?>

    <?php // echo $form->field($model, 'store_rid')->dropDownList(ArrayHelper::map(api\common\models\RkStore::find()->all(), 'rid', 'denom')) ?>

    <?php
    yii::$app->db_api->
    createCommand()->
    update('rk_storetree', ['disabled' => '0', 'collapsed' => '1'], 'acc=' . Yii::$app->user->identity->organization_id . ' and active = 1 and type = 2')->execute();
    ?>

    <div class="box-body table-responsive no-padding" style="overflow-x:visible; overflow-y:visible;">
        <?php echo $form->field($model, 'store_rid')->widget(TreeViewInput::classname(),
            [
                'name'        => 'store_rid',
                'value'       => 'true', // preselected values
                'query'       => api\common\models\RkStoretree::find()->andWhere('acc = :acc', [':acc' => $org])->addOrderBy('root, lft'),
                //  'headingOptions' => ['label' => 'Склады'],
                'rootOptions' => ['label' => 'Справочник складов'],
                'fontAwesome' => true,
                'asDropdown'  => false,
                // 'dropdownConfig' => [],
                'multiple'    => false,
                'options'     => ['disabled' => false]
            ]);

        ?>
    </div>
    <?php

    if (!$model->doc_date) {
        //  $model->doc_date = date('d.m.Y', time()); // Добавить каскадную проверку на даты здесь
        $rdate = date('d.m.Y', strtotime($model->getFinalDate()));
    } else {
        $rdate = date('d.m.Y', strtotime($model->doc_date));
        //  var_dump($rdate);
        // $rdate->format('m/d/y h:i a');
    }
    $model->doc_date = $rdate;

    ?>
    <?php echo $form->field($model, 'doc_date')->label('Дата Документа')->
    widget(DatePicker::classname(), [
        'type'          => DatePicker::TYPE_COMPONENT_APPEND,
        'convertFormat' => true,
        'layout'        => '{picker}{input}',
        //   'disabled'=>$disable,
        'pluginOptions' => [
            'autoclose'      => true,
            //   'format' => 'Y-m-d',
            'format'         => 'dd.MM.yyyy',
            //     'format' => 'yyyy.MM.dd',
            //    'startDate' => $model->startDate,
            //    'endDate' => $model->endDate,
            'todayHighlight' => false,

        ],
    ]);

    ?>

    <?php echo $form->field($model, 'note')->textInput(['maxlength' => true]) ?>


    <?php // echo $form->field($model, 'num_code')->hiddenInput(['value' => Yii::$app->user->identity->userProfile->branch_id])->label(''); ?>


    <div class="form-group">
        <?php echo Html::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        <?= Html::a('Вернуться',
            Url::previous(),
            ['class' => 'btn btn-success btn-export']);
        ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

