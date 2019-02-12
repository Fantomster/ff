<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\Allow;
use common\models\Role;
use kartik\checkbox\CheckboxX;

/* @var $this yii\web\View */
/* @var $model common\models\User */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="user-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($profile, 'full_name') ?>

    <?=
    $form->field($profile, 'gender')->dropDownList(common\models\Gender::getList());
    ?>

    <?php

    switch ($user->role_id) {
        case Role::ROLE_RESTAURANT_MANAGER:
            print $form->field($profile, 'job_id')->dropDownList(common\models\Job::getListRestor());
            break;
        case Role::ROLE_RESTAURANT_EMPLOYEE:
            print $form->field($profile, 'job_id')->dropDownList(common\models\Job::getListRestor());
            break;
        case Role::ROLE_RESTAURANT_EMPLOYEE:
            print $form->field($profile, 'job_id')->dropDownList(common\models\Job::getListRestor());
            break;
        case Role::ROLE_SUPPLIER_MANAGER:
            print $form->field($profile, 'job_id')->dropDownList(common\models\Job::getListPostav());
            break;
        case Role::ROLE_SUPPLIER_EMPLOYEE:
            print $form->field($profile, 'job_id')->dropDownList(common\models\Job::getListPostav());
            break;
        default:
            print $form->field($profile, 'job_id');
    }
    //$form->field($profile, 'job_id')
    ?>
    <?php
    if($currentUser->role_id == ROLE::ROLE_ADMIN) {
        echo $form->field($user, 'role_id')->dropDownList($dropDown, ['options' => [$selected => ['Selected' => true]]])->label(Yii::t('message', 'frontend.views.client.settings.role', ['ru' => "Роль"]));
    }
      ?>
    <?php //echo $form->field($profile, 'email') ?>

    <?= $form->field($profile, 'phone')->widget(\common\widgets\phone\PhoneInput::className(), [
        'jsOptions' => [
            'preferredCountries' => ['ru'],
            'nationalMode' => false,
            'utilsScript' => Yii::$app->assetManager->getPublishedUrl('@bower/intl-tel-input') . '/build/js/utils.js',
        ],
        'options' => [
            'class' => 'form-control',
        ],
    ]) ?>

    <?=
    $form->field($user, 'subscribe')->dropDownList(common\models\Allow::getList());
    ?>

    <?=
    $form->field($user, 'sms_subscribe')->dropDownList(common\models\Allow::getList());
    ?>


    <?=
            $form->field($user, 'status')->dropDownList(common\models\User::statusDropdown());
//    $form->field($user, 'status')->widget(CheckboxX::classname(), [
//        //'initInputType' => CheckboxX::INPUT_CHECKBOX,
//        'autoLabel' => true,
//        'model' => $user,
//        'attribute' => 'status',
//        'pluginOptions' => [
//            'threeState' => false,
//            'theme' => 'krajee-flatblue',
//            'enclosedLabel' => false,
//            'size' => 'md',
//        ],
//        'labelSettings' => [
//            'label' => 'Активен',
//            'position' => CheckboxX::LABEL_RIGHT,
//            'options' => ['style' => '']
//        ]
//    ])->label(false);
    ?>

    <?= $user->role_id === common\models\Role::ROLE_FKEEPER_MANAGER ? $form->field($user, 'organization_id')->textInput(['maxlength' => true]) : '' ?>

    <div class="form-group">
        <?= Html::submitButton($user->isNewRecord ? 'Create' : 'Update', ['class' => $user->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
