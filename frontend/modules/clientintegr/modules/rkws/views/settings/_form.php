<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\date\DatePicker;
use yii\helpers\ArrayHelper;
use kartik\tree\TreeViewInput;
use yii\bootstrap\Dropdown;
use common\models\User;

/* @var $this yii\web\View */
/* @var $model common\models\pdict\DictAgent */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="dict-agent-form">

    <?php $org = User::findOne(Yii::$app->user->id)->organization_id; // var_dump($org); ?>

    <?php $pConst = \api\common\models\RkDicconst::findOne(['id' => $model->const_id]); ?>

    <?php $form = ActiveForm::begin(); ?>

    <?php echo $form->errorSummary($model); ?>

    <?php // echo $form->field($model, 'id')->textInput(['maxlength' => true,'disabled' => 'disabled']) ?>

    <?php // echo $form->field($model, 'const_id')->textInput(['maxlength' => true,'disabled' => 'disabled']) ?>

    <?php switch ($pConst->type) {

        case \api\common\models\RkDicconst::PC_TYPE_DROP :

            if ($pConst->denom === 'taxVat') {

                echo $form->field($model, 'value')->dropDownList([
                    '0'    => '0',
                    '1000' => '10',
                    '1800' => '18'
                ]);
            } elseif ($pConst->denom === 'auto_unload_invoice') {
                echo $form->field($model, 'value')->dropDownList([
                    '0' => 'Выключено',
                    '1' => 'Включено',
                    '2' => 'Полуавтомат',
                ]);
            } elseif ($pConst->denom === 'sh_version') {
                echo $form->field($model, 'value')->dropDownList(\api\common\models\RkDicconst::SH_VERSION);
            } else {
                echo $form->field($model, 'value')->dropDownList([
                    '0' => Yii::t('app', 'frontend.modules.form.off', ['ru' => 'Выключено']),
                    '1' => Yii::t('app', 'frontend.modules.form.on', ['ru' => 'Включено']),
                ]);
            } ?>
            <?php break;
        case \api\common\models\RkDicconst::PC_TYPE_TREE :

            yii::$app->db_api->
            createCommand()->
            update('rk_category', ['disabled' => '0'], 'acc=' . Yii::$app->user->identity->organization_id . ' and active = 1')->execute();

            echo $form->field($model, 'value')->widget(TreeViewInput::classname(),
                [
                    'name'           => 'category_list',
                    'value'          => 'true', // preselected values
                    'query'          => \api\common\models\RkCategory::find()
                        ->andWhere('acc = :acc', [':acc' => User::findOne([Yii::$app->user->id])->organization_id])
                        ->andWhere('active = 1')
                        ->addOrderBy('root, lft'),
                    'headingOptions' => ['label' => 'Группы номенклатуры'],
                    'rootOptions'    => ['label' => ''],
                    'fontAwesome'    => true,
                    'asDropdown'     => false,
                    'multiple'       => true,
                    'options'        => ['disabled' => false]
                ]);

            echo Html::hiddenInput('isTree', 1);
            echo Html::hiddenInput('treeName', $pConst->denom);

            break;

        default: ?>

            <?php echo $form->field($model, 'value')->textInput(['maxlength' => true]) ?>

        <?php } ?>


    <div class="form-group">
        <?php echo Html::submitButton($model->isNewRecord ? Yii::t('app', 'frontend.modules.form.create', ['ru' => 'Создать']) : Yii::t('app', 'frontend.modules.form.save', ['ru' => 'Сохранить']), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        <?= Html::a('Вернуться',
            ['index'],
            ['class' => 'btn btn-success btn-export']);
        ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

