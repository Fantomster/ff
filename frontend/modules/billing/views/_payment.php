<?php
    use yii\helpers\Html;

    $types = \yii\helpers\ArrayHelper::map(\common\models\PaymentType::find()->asArray()->all(), 'type_id', 'title');
    $types[0] = '---';
    ksort($types);

?>
<div class="well">
    <input type="hidden" value="<?= Yii::$app->request->getAbsoluteUrl() ?>"
           id="return_url_billing">
    <div class="form-group">
        <div class="form-group field-amount_billing required">
            <label class="control-label" for="amount_billing">Сумма платежа</label>
            <div class="input-group">
                                            <span class="input-group-addon">
                                                <i class="fa fa-money"></i>
                                            </span>
                <input type="text" id="amount_billing" class="form-control"
                       placeholder="Сумма платежа..">
            </div>
        </div>
    </div>
    <br>
    <div class="form-group">
        <div class="form-group field-payment_type_id_billing required">
            <label class="control-label" for="payment_type_id_billing">Тип платежа</label>
            <?php
            echo Html::dropDownList(
                'payment_type_id_billing',
                1,
                $types,
                [
                    'id' => 'payment_type_id_billing',
                    'class' => 'form-control'
                ]
            );
            ?>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label" for="payment_type_id">&nbsp;</label>
        <div class="form-group field-button text-right">
            <button id="pay_button_billing" class="btn btn-success">
                Оплатить
            </button>
        </div>
    </div>
</div>

<?php
$js = "
    $('#pay_button_billing').click(function(){
        var button = $(this);
        button.attr('disabled', 'disabled');
        button.text('Загрузка...');
        $.post('/billing/payment/create', {
            amount: $('#amount_billing').val(), 
            payment_type_id: $('#payment_type_id_billing').val(),
            return_url: $('#return_url_billing').val()
        },
        function(data) {
            if(data && data.confirmation && data.confirmation.confirmation_url) {
                location.href = data.confirmation.confirmation_url;
            } else {
                button.removeAttr('disabled');
                button.text('Оплатить');
            }
        });
    }); 
";
$this->registerJs($js);
?>