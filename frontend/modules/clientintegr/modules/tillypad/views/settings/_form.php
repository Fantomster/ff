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
                        '0'    => '0',
                        '1000' => '10',
                        '1800' => '18'
                    ]);
                } elseif ($dicConst->denom === 'auto_unload_invoice') {
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
                $iikoSelectedStores = \api\common\models\iiko\iikoSelectedStore::findAll(['organization_id' => $org]);
                if ($iikoSelectedStores) {
                    foreach ($iikoSelectedStores as $iikoStore) {
                        $arr[] = $iikoStore->store_id;
                    }
                }

                $iikoStores = \api\common\models\iiko\iikoStore::findAll(['org_id' => $org, 'is_active' => 1]);
                if ($iikoStores && is_iterable($iikoStores)) {
                    foreach ($iikoStores as $store) {
                        echo $form->field($model, 'value')->checkbox(['label' => $store->denom, 'value' => (is_iterable($arr) && in_array($store->id, $arr)) ? true : false, 'name' => 'Stores[' . $store->id . ']', 'checked ' => (is_iterable($arr) && in_array($store->id, $arr)) ? true : false]);
                    }
                }
                break;
            case \api\common\models\iiko\iikoDicconst::TYPE_LIST:
                echo $this->render('_goods_list', ['org' => $org, 'id' => $id, 'productSearch' => $productSearch, 'cookingPlaceSearch' => $cookingPlaceSearch, 'unitSearch' => $unitSearch, 'sort' => $sort, 'page' => $page]);
                break;
            case \api\common\models\iiko\iikoDicconst::TYPE_INT:
                echo $form->field($model, 'value')->input('number', ['maxlength' => true]);
                break;
            default:
                echo $form->field($model, 'value')->textInput(['maxlength' => true]);
        }
        ?>
        <div class="form-group">
            <?php
            if ($id == 7) {
                echo Html::buttonInput($model->isNewRecord ? 'Создать' : 'Сохранить', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']);
            } else {
                echo Html::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']);
            }
            ?>
            <?= Html::a('Вернуться', ['index'], ['class' => 'btn btn-success btn-export']); ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
<?php
$customJs = <<< JS

$("document").ready(function(){
$(".dict-agent-form").on("click", "input[type='checkbox']", function() {
    var checked = $(this).prop('checked');
    if (checked) {
        $(this).val(1);
    } else {
        $(this).val(0);
    }
});
});

JS;
$this->registerJs($customJs, \yii\web\View::POS_READY);

