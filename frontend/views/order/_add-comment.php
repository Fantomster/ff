<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$form = ActiveForm::begin([
            'options' => [
                'id' => 'commentForm',
            ],
            'action' => Url::to(['order/ajax-set-comment']),
        ]);
echo Html::hiddenInput('order_id', $order->id);
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title">Комментарий к заказу</h4>
</div>
<div class="modal-body form-inline" style="text-align: center;"> 
    <?=
        $form->field($order, 'comment')->label(false)->textarea(['style' => 'width: 400px; height: 100px;']);
    ?>
</div>
<div class="modal-footer">
    <a href="#" class="btn btn-gray" data-dismiss="modal"><i class="icon fa fa-remove"></i> Закрыть</a>
    <?= Html::button('<i class="icon fa fa-save"></i> Сохранить', ['class' => 'btn btn-success saveComment', 'data' => ['dismiss' => "modal"]]) ?>
</div>
<?php
ActiveForm::end();
?>