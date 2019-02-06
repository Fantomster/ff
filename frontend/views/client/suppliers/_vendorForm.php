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
<?= $form->field($user, 'email')->textInput(['class' => 'input_type_1_1', 'placeholder' => Yii::t('message', 'frontend.views.client.supp.email', ['ru'=>'Введите E-mail поставщика'])]) ?>
<?= $form->field($profile, 'full_name')->textInput(['class' => 'input_type_2_1', 'placeholder' => Yii::t('message', 'frontend.views.client.supp.fio', ['ru'=>'Введите ФИО поставщика']), 'disabled' => $disabled]) ?>
<?=
        $form->field($profile, 'phone')
        ->widget(\common\widgets\phone\PhoneInput::className(), [
            'jsOptions' => [
                'preferredCountries' => ['ru'],
                'nationalMode' => false,
                'utilsScript' => Yii::$app->assetManager->getPublishedUrl('@bower/intl-tel-input') . '/build/js/utils.js',
            ],
        ])
        ->textInput(['class' => 'input_type_2_2', 'placeholder' => Yii::t('message', 'frontend.views.client.supp.phone', ['ru'=>'Телефон поставщика']), 'disabled' => $disabled])
?>
<?= $form->field($organization, 'name')->textInput(['class' => 'input_type_1_2', 'placeholder' => Yii::t('message', 'frontend.views.client.supp.org', ['ru'=>'Введите название организации поставщика']), 'disabled' => $disabled]) ?>
<?=
Html::button(Yii::t('message', 'frontend.views.client.supp.add_goods', ['ru'=>'Добавить продукты']), [
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
<?= Html::button(Yii::t('message', 'frontend.views.client.supp.invite', ['ru'=>'Пригласить']), ['class' => 'submit', 'style'=> 'display: none;', 'name' => 'inviteSupplier', 'id' => 'inviteSupplier', 'data-loading-text'=>"<span class='glyphicon-left glyphicon glyphicon-refresh spinning'></span> " . Yii::t('message', 'frontend.views.client.supp.inviting', ['ru'=>'Приглашаем...'])]) ?>
<br>
<?php ActiveForm::end(); ?>
