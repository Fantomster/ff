<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$form = ActiveForm::begin([
            'options' => [
                'id' => 'commentForm',
            ],
            'action' => Url::to(['order/ajax-cancel-order']),
        ]);
echo Html::hiddenInput('order_id', $order->id);
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title"><?= Yii::t('message', 'frontend.views.order.really', ['ru'=>'Действительно отменить заказ?']) ?></h4>
</div>
<div class="modal-body form-inline" style="text-align: center;"> 
    <?=
        $form->field($order, 'comment', ['options' => ['style' => 'width: 100%;']])
            ->label(false)
            ->textarea(['style' => 'width: 100%; min-width: 300px; height: 100px;', 'placeholder' => Yii::t('message', 'frontend.views.order.comment_two', ['ru'=>'Комментарий'])]);
    ?>
</div>
<div class="modal-footer">
    <?= Html::button('<i class="icon fa fa-save"></i> ' . Yii::t('message', 'frontend.views.order.yep_four', ['ru'=>'Да']), ['class' => 'btn btn-success saveComment', 'data' => ['dismiss' => "modal"]]) ?>
    <a href="#" class="btn btn-gray" data-dismiss="modal"><i class="icon fa fa-remove"></i> <?= Yii::t('message', 'frontend.views.order.nope', ['ru'=>'Нет']) ?></a>
</div>
<?php
ActiveForm::end();
?>