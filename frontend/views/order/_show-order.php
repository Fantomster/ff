<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\widgets\TouchSpin;

$form = ActiveForm::begin([
            'options' => [
                'id' => 'order-form',
                'class' => "navbar-form",
            ],
        ]);
echo Html::hiddenInput('vendor_id', $showOrder['vendor_id']);
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title">Заказ у <?= $showOrder['vendor_name'] ?></h4>
</div>
<div class="modal-body">
    <table class="table table-striped">
        <tbody>
            <?php
            foreach ($showOrder['content'] as $product) {
                ?>
                <tr>
    <?= Html::hiddenInput("content[$product[product_id]][product_id]", $product['product_id']) ?>
                    <td><?= Html::label($product['product_name']) ?></td>
                    <td><?=
                        TouchSpin::widget([
                            'name' => "content[$product[product_id]][quantity]",
                            'id' => "qty$product[product_id]",
                            'pluginOptions' => [
                                'initval' => $product['quantity'],
                                'min' => 0,
                                'step' => 1,
                                'decimals' => 0,
                                'buttonup_class' => 'btn btn-primary',
                                'buttondown_class' => 'btn btn-info',
                                'buttonup_txt' => '<i class="glyphicon glyphicon-plus-sign"></i>',
                                'buttondown_txt' => '<i class="glyphicon glyphicon-minus-sign"></i>'
                            ],
                            'options' => ['style' => 'width: 70px;'],
                        ])
                        ?></td>
                    <td><?= Html::label($product['price']) ?></td>
                </tr>
                <?php }
            ?>
        </tbody>
    </table>
</div>
<div class="modal-footer">
    <a href="#" class="btn btn-primary" data-dismiss="modal">Закрыть</a>
    <?= Html::button('Сохранить', ['class' => 'btn btn-primary saveOrder']) ?>
    <?= Html::button('Удалить', ['class' => 'btn btn-danger clearOrder']) ?>
    <?= Html::button('Отправить', ['class' => 'btn btn-success sendOrder']) ?>
</div>
<?php
ActiveForm::end();
?>