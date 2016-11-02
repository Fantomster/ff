<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$form = ActiveForm::begin([
            'options' => [
                'id' => 'noteForm',
            ],
            'action' => Url::to(['order/ajax-set-note']),
        ]);
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title">Комментарий к продукту</h4>
</div>
<div class="modal-body form-inline" style="text-align: center;"> 
    <?php
        echo $form->field($note, 'catalog_base_goods_id')->hiddenInput()->label(false);
        echo $form->field($note, 'note', ['options' => ['style' => 'width: 100%;']])->label(false)->textarea(['style' => 'width: 100%; min-width: 300px; height: 100px;']);
    ?>
</div>
<div class="modal-footer">
    <?= Html::button('<i class="icon fa fa-save"></i> Сохранить', ['class' => 'btn btn-success saveNote', 'data' => ['dismiss' => "modal"]]) ?>
    <a href="#" class="btn btn-gray" data-dismiss="modal"><i class="icon fa fa-remove"></i> Закрыть</a>
</div>
<?php
ActiveForm::end();
?>