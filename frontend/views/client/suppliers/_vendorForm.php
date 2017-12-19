<?php
use kartik\form\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

$form = ActiveForm::begin([
            'id' => 'SuppliersFormSend',
            'enableClientValidation' => true,
            'enableAjaxValidation' => false,
            'action' => Url::to(['client/ajax-validate-vendor']),
            'validateOnSubmit' => false,
            'fieldConfig' => [
                'template' => '{input}',
                'options' => [
                    'tag' => false,
                ],
            ],
        ]);
?>
<?= $form->field($user, 'email')->textInput(['class' => 'input_type_1_1', 'placeholder' => 'Введите E-mail поставщика']) ?>
<?= $form->field($profile, 'full_name')->textInput(['class' => 'input_type_2_1', 'placeholder' => 'Введите ФИО поставщика', 'disabled' => $disabled]) ?>
<?=
        $form->field($profile, 'phone')
        ->widget(\common\widgets\PhoneInput::className(), [
            'jsOptions' => [
                'preferredCountries' => ['ru'],
                'nationalMode' => false,
                'utilsScript' => Yii::$app->assetManager->getPublishedUrl('@bower/intl-tel-input') . '/build/js/utils.js',
            ],
        ])
        ->textInput(['class' => 'input_type_2_2', 'placeholder' => 'Телефон поставщика', 'disabled' => $disabled])
?>
<?= $form->field($organization, 'name')->textInput(['class' => 'input_type_1_2', 'placeholder' => 'Введите название организации поставщика', 'disabled' => $disabled]) ?>
<?=
Html::button('Добавить продукты', [
    'class' => 'submit',
    'disabled' => $disabled,
    'name' => 'addProduct',
    'id' => 'addProduct',
    'data' => [
        'target' => '#modal_addProduct',
        'toggle' => 'modal',
        'backdrop' => 'static',
    ],
]);//data-loading-text="<span class='glyphicon-left glyphicon glyphicon-refresh spinning'></span> Регистрируемся..."
?>
<?= Html::submitButton('fake', ['class' => 'hide']) ?>
<?= Html::button('Пригласить', ['class' => 'submit', 'style'=> 'display: none;', 'name' => 'inviteSupplier', 'id' => 'inviteSupplier', 'data-loading-text'=>"<span class='glyphicon-left glyphicon glyphicon-refresh spinning'></span> Приглашаем..."]) ?>
<br>
<?php ActiveForm::end(); ?>
