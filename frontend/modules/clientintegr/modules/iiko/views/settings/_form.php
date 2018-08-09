<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\models\User;
use yii\widgets\Pjax;


/* @var $this yii\web\View */
/* @var $model \api\common\models\iiko\iikoPconst */
/* @var $dicConst \api\common\models\iiko\iikoDicconst */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="dict-agent-form">
    <?php $org = User::findOne(Yii::$app->user->id)->organization_id; ?>
    <?php $form = ActiveForm::begin(); ?>
    <?php echo $form->errorSummary($model); ?>
    <?php
    switch ($dicConst->type) {
        case \api\common\models\iiko\iikoDicconst::TYPE_DROP :
            if ($dicConst->denom === 'taxVat') {
                echo $form->field($model, 'value')->dropDownList([
                    '0' => '0',
                    '1000' => '10',
                    '1800' => '18'
                ]);
            } else if ($dicConst->denom === 'auto_unload_invoice') {
                echo $form->field($model, 'value')->dropDownList([
                    '0' => 'Выключено',
                    '1' => 'Включено',
                    '2' => 'Полуавтомат',
                ]);
            } else {
                echo $form->field($model, 'value')->dropDownList([
                    '0' => 'Выключено',
                    '1' => 'Включено',
                ]);
            }
            break;
        case \api\common\models\iiko\iikoDicconst::TYPE_PASSWORD:
            echo $form->field($model, 'value')->passwordInput(['maxlength' => true]);
            break;
        case \api\common\models\iiko\iikoDicconst::TYPE_CHECKBOX:
            $arr = [];
            $iikoPconst = \api\common\models\iiko\iikoPconst::find()->leftJoin('iiko_dicconst', 'iiko_dicconst.id=iiko_pconst.const_id')->where('iiko_dicconst.denom="available_stores_list"')->andWhere('iiko_pconst.org=' . $org)->one();
            if ($iikoPconst && !empty($iikoPconst->value)) {
                try {
                    $arr = unserialize($iikoPconst->value);
                } catch (Exception $e) {
                    return $e->getMessage();
                }
            }
            $iikoStores = \api\common\models\iiko\iikoStore::findAll(['org_id' => $org, 'is_active' => 1]);
            if ($iikoStores && is_iterable($iikoStores)) {
                foreach ($iikoStores as $store) {
                    echo $form->field($model, 'value')->checkbox(['label' => $store->denom, 'name' => 'Stores[' . $store->id . ']', 'checked ' => (is_iterable($arr) && in_array($store->id, $arr)) ? true : false]);
                }
            }
            break;
        case \api\common\models\iiko\iikoDicconst::TYPE_LIST:
            echo $this->render('_goods_list', ['org' => $org, 'id' => $id]);
            break;
        default:
            echo $form->field($model, 'value')->textInput(['maxlength' => true]);
    }
    ?>
    <div class="form-group">
        <?php echo Html::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        <?= Html::a('Вернуться', ['index'], ['class' => 'btn btn-success btn-export']); ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>

