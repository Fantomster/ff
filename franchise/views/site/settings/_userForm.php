<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use common\models\Role;
use kartik\checkbox\CheckboxX;

kartik\checkbox\KrajeeFlatBlueThemeAsset::register($this);
?>
<div class="modal-dialog">
    <div class="model-content">
        <?php
        $form     = ActiveForm::begin([
                    'id'                     => 'user-form',
                    'enableAjaxValidation'   => true,
                    'enableClientValidation' => true,
                    'action'                 => $user->isNewRecord ? Url::toRoute('site/ajax-create-user') : Url::toRoute(['site/ajax-update-user', 'id' => $user->id]),
                    'options'                => [
                        'class' => 'user-form',
                    ],
                    'validationUrl'          => Url::toRoute('site/ajax-validate-user'),
        ]);
        ?>
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h4 class="modal-title"><?= $user->isNewRecord ? Yii::t('app', 'franchise.views.site.settings.new_user', ['ru' => 'Новый пользователь']) : Yii::t('app', 'franchise.views.site.settings.edit_user', ['ru' => 'Редактировать пользователя']) ?></h4>
        </div>
        <div class="modal-body">
            <input type="email"  name="Userito[email]" style="position: absolute; top: -100%;">
            <input type="password" name="new-password" style="position: absolute; top: -100%;">

            <?= $form->field($user, 'email') ?>

            <?= $form->field($user, 'newPassword')->passwordInput() ?>

            <?= $form->field($profile, 'full_name')->textInput(['value' => Html::decode($profile->full_name),]) ?>

            <?=
            $form->field($profile, 'phone')->widget(\common\widgets\phone\PhoneInput::className(), [
                'jsOptions' => [
                    'preferredCountries' => ['ru'],
                    'nationalMode'       => false,
                    'utilsScript'        => Yii::$app->assetManager->getPublishedUrl('@bower/intl-tel-input') . '/build/js/utils.js',
                ],
                'options'   => [
                    'class' => 'form-control',
                ],
            ])
            ?>

            <?=
            $form->field($profile, 'sms_allow')->widget(CheckboxX::classname(), [
                //'initInputType' => CheckboxX::INPUT_CHECKBOX,
                'autoLabel'     => true,
                'model'         => $profile,
                'attribute'     => 'sms_allow',
                'pluginOptions' => [
                    'threeState'    => false,
                    'theme'         => 'krajee-flatblue',
                    'enclosedLabel' => false,
                    'size'          => 'md',
                ],
                'labelSettings' => [
                    'label'    => Yii::t('app', 'franchise.views.site.settings.allow_sms', ['ru' => 'Разрешить СМС уведомление']),
                    'position' => CheckboxX::LABEL_RIGHT,
                    'options'  => ['style' => '']
                ]
            ])->label(false);
            ?>

            <?= $form->field($user, 'role_id')->dropDownList(common\models\Franchisee::limitedDropdown())->label(Yii::t('app', 'franchise.views.site.settings.role', ['ru' => 'Роль'])) ?>

            <div style="display: <?= (isset($user->role_id) && $user->role_id == Role::ROLE_FRANCHISEE_MANAGER) ? 'block' : 'none' ?>" class="alLeaderChoose">

                <?= $form->field($rel, 'leader_id')->dropDownList($leadersArray, ['prompt' => Yii::t('app', 'franchise.views.site.settings.choose_boss', ['ru' => 'Выберите руководителя для менеджера'])])->label(Yii::t('app', 'franchise.views.site.settings.boss', ['ru' => 'Руководитель'])) ?>

                <?= $form->field($rel, 'manager_id')->hiddenInput(['value' => $user->id])->label(false) ?>

            </div>

            <?=
            $form->field($user, 'status')->widget(CheckboxX::classname(), [
                //'initInputType' => CheckboxX::INPUT_CHECKBOX,
                'autoLabel'     => true,
                'model'         => $user,
                'attribute'     => 'status',
                'pluginOptions' => [
                    'threeState'    => false,
                    'theme'         => 'krajee-flatblue',
                    'enclosedLabel' => false,
                    'size'          => 'md',
                ],
                'labelSettings' => [
                    'label'    => Yii::t('app', 'franchise.views.site.settings.is_active', ['ru' => 'Активен']),
                    'position' => CheckboxX::LABEL_RIGHT,
                    'options'  => ['style' => '']
                ]
            ])->label(false);
            ?>


        </div>
        <div class="modal-footer">
            <?= Html::button($user->isNewRecord ? '<i class="icon fa fa-user-plus"></i> ' . Yii::t('app', 'franchise.views.site.settings.create', ['ru' => 'Создать']) . ' ' : '<i class="icon fa fa-save"></i> ' . Yii::t('app', 'franchise.views.site.settings.save', ['ru' => 'Сохранить']) . ' ', ['class' => 'btn btn-success edit']) ?>
            <?=
            Html::button('<i class="fa fa-fw fa-trash-o"></i> ' . Yii::t('app', 'franchise.views.site.settings.del', ['ru' => 'Удалить']) . ' ', [
                'class' => 'btn btn-danger delete',
                'data'  => [
                    'id'     => $user->id,
                    'action' => Url::to(["site/ajax-delete-user"]),
        ]])
            ?>
            <a href="#" class="btn btn-gray" data-dismiss="modal"><i class="icon fa fa-ban"></i> <?= Yii::t('app', 'franchise.views.site.settings.cancel', ['ru' => 'Отмена']) ?></a>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
<?php
$role_id  = Role::ROLE_FRANCHISEE_MANAGER;
$customJs = <<< JS

$('#user-role_id').on("change", function () {
    var role_id = '$role_id';
    if($(this).val()==role_id){
        $('.alLeaderChoose').css('display', 'block');
    }else{
        $('.alLeaderChoose').css('display', 'none');
    }
});

JS;
$this->registerJs($customJs, \yii\web\View::POS_READY);
