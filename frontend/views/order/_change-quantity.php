<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\widgets\TouchSpin;

$form = ActiveForm::begin([
            'options' => [
                'id' => 'quantityForm',
            ],
            'action' => Url::to(['order/ajax-change-quantity']),
        ]);
echo Html::hiddenInput('vendor_id', $vendor_id);
echo Html::hiddenInput('product_id', $product_id);
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title">Изменить количество</h4>
</div>
<div class="modal-body form-inline" style="text-align: center;"> 
    <?= Html::label("<b>$product_name</b> ($vendor_name)", '', ['class' => 'padding-right-15']) ?>
                    <?=
                        TouchSpin::widget([
                            'name' => "quantity",
                            'id' => "qty$product_id",
                            'pluginOptions' => [
                                'initval' => $quantity,
                                'min' => (isset($units) && ($units > 0)) ? $units : 0.001,
                                'max' => PHP_INT_MAX,
                                'step' => (isset($units) && ($units)) ? $units : 1,
                                'decimals' => (!isset($units) || (fmod($units, 1) > 0)) ? 3 : 0,
                                'forcestepdivisibility' => (isset($units) && ($units)) ? 'floor' : 'none',
                                'buttonup_class' => 'btn btn-default',
                                'buttondown_class' => 'btn btn-default',
                                'buttonup_txt' => '<i class="glyphicon glyphicon-plus-sign"></i>',
                                'buttondown_txt' => '<i class="glyphicon glyphicon-minus-sign"></i>'
                            ],
                          //  'options' => ['style' => 'width: 100px;'],
                        ])
                        ?>
</div>
<div class="modal-footer">
    <?= Html::button('<i class="icon fa fa-save"></i> Сохранить', ['class' => 'btn btn-success save', 'data' => ['dismiss' => "modal"]]) ?>
    <a href="#" class="btn btn-gray" data-dismiss="modal"><i class="icon fa fa-remove"></i> Закрыть</a>
</div>
<?php
ActiveForm::end();
?>