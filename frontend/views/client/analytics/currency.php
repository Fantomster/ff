<?php if($count): ?>
    <?= \yii\helpers\Html::label(Yii::t('message', 'frontend.views.client.anal.currency', ['ru' => 'Валюта']), null, ['class' => 'label', 'style' => 'color:#555']) ?>
    <?=
    \yii\helpers\Html::dropDownList('filter_currency', null, $currencyList, ['class' => 'form-control', 'id' => 'filter_currency'])
    ?>
<?php endif; ?>