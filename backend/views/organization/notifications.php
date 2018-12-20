<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\checkbox\CheckboxX;

$this->title = Yii::t('message', 'frontend.views.settings.notifications_three', ['ru' => 'Уведомления']);
$this->params['breadcrumbs'][] = ['label' => 'Организации', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<section class="content-header">
    <h1>
        <i class="fa fa-gears"></i> <?= Yii::t('message', 'frontend.views.settings.notifications', ['ru' => 'Уведомления']) ?>
        <small><?= Yii::t('message', 'frontend.views.settings.notifications_settings', ['ru' => 'Настройка уведомлений']) ?></small>
    </h1>
</section>
<section class="content">
    <div class="box box-info settings">
        <?php
        $form = ActiveForm::begin([
            'enableAjaxValidation' => false,
            'method' => 'post',
        ]);
        ?>
        <div class="box-body">
            <?php foreach ($users as $user){
            $emailNotification = $user->getEmailNotification($id);
            $smsNotification = $user->getSmsNotification($id);
            if(!$emailNotification->id || !$smsNotification->id)continue;
            ?>
            <div class="row">
                <div class="col-md-6">
                    <h2><?= $user->email ?? '' ?></h2>
                    <br>
                    <?= $form->field($emailNotification, 'order_created')->checkbox(['label' => Yii::t('message', 'frontend.views.settings.new_order', ['ru' => 'Уведомление о новом заказе по email']), 'name' => 'Email[' . $user->id . '][order_created]'
                    ]) ?>
                    <?= $form->field($emailNotification, 'order_canceled')->checkbox(['label' => Yii::t('message', 'frontend.views.settings.order_cancel_notify', ['ru' => 'Уведомление об отмене заказа по email']), 'name' => 'Email[' . $user->id . '][order_canceled]'
                    ]) ?>
                    <?= $form->field($emailNotification, 'order_changed')->checkbox(['label' => Yii::t('message', 'frontend.views.settings.notify_changes', ['ru' => 'Уведомление об изменениях в заказе по email']), 'name' => 'Email[' . $user->id . '][order_changed]'
                    ]) ?>
                    <?= $form->field($emailNotification, 'order_processing')->checkbox(['label' => Yii::t('message', 'frontend.views.settings.notify_begin', ['ru' => 'Уведомление о начале выполнения заказа по email']), 'name' => 'Email[' . $user->id . '][order_processing]'
                    ]) ?>
                    <?= $form->field($emailNotification, 'order_done')->checkbox(['label' => Yii::t('message', 'frontend.views.settings.notify_end', ['ru' => 'Уведомление о завершении заказа по email']), 'name' => 'Email[' . $user->id . '][order_done]'
                    ]) ?>
                    <?= $form->field($emailNotification, 'request_accept')->checkbox(['label' => Yii::t('app', 'frontend.views.settings.notifications.note_two', ['ru' => 'Уведомления о назначении исполнителем заявки по email']), 'name' => 'Email[' . $user->id . '][request_accept]'
                    ]) ?>
                    <?php if ($user->role_id == \common\models\Role::ROLE_SUPPLIER_MANAGER) {
                        echo $form->field($emailNotification, 'receive_employee_email')->checkbox(['label' => Yii::t('app', 'frontend.views.settings.notify_receive_email', ['ru' => 'Получать email, если запрос на сотрудничество направлен на почту не мне, а моему работнику']), 'name' => 'Email[' . $user->id . '][receive_employee_email]'
                        ]);
                    } ?>
                    <?= $form->field($user, 'subscribe')->checkbox(['label' => Yii::t('app', 'frontend.views.settings.info_mail', ['ru' => 'Информационные рассылки по email']), 'name' => 'User[' . $user->id . '][subscribe]'
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <h2><?= $user->profile->phone ?? '' ?></h2>
                    <br>
                    <?= $form->field($smsNotification, 'order_created')->checkbox(['label' => Yii::t('message', 'frontend.views.settings.sms_notify', ['ru' => 'Уведомление о новом заказе по sms']), 'name' => 'Sms[' . $user->id . '][order_created]'
                    ]) ?>
                    <?= $form->field($smsNotification, 'order_canceled')->checkbox(['label' => Yii::t('message', 'frontend.views.settings.sms_cancel_order', ['ru' => 'Уведомление об отмене заказа по sms']), 'name' => 'Sms[' . $user->id . '][order_canceled]'
                    ]) ?>
                    <?= $form->field($smsNotification, 'order_changed')->checkbox(['label' => Yii::t('message', 'frontend.views.settings.sms_changes', ['ru' => 'Уведомление об изменениях в заказе по sms']), 'name' => 'Sms[' . $user->id . '][order_changed]'
                    ]) ?>
                    <?= $form->field($smsNotification, 'order_processing')->checkbox(['label' => Yii::t('message', 'frontend.views.settings.order_sms_begin', ['ru' => 'Уведомление о начале выполнения заказа по sms']), 'name' => 'Sms[' . $user->id . '][order_processing]'
                    ]) ?>
                    <?= $form->field($smsNotification, 'order_done')->checkbox(['label' => Yii::t('message', 'frontend.views.settings.order_end_sms', ['ru' => 'Уведомление о завершении заказа по sms']), 'name' => 'Sms[' . $user->id . '][order_done]'
                    ]) ?>
                    <?= $form->field($smsNotification, 'request_accept')->checkbox(['label' => Yii::t('app', 'frontend.views.settings.notifications.note_four', ['ru' => 'Уведомления о назначении исполнителем заявки по sms']), 'name' => 'Sms[' . $user->id . '][request_accept]'
                    ]) ?>
                    <?php if ($user->role_id == \common\models\Role::ROLE_SUPPLIER_MANAGER) {
                        echo $form->field($smsNotification, 'receive_employee_sms')->checkbox(['label' => Yii::t('app', 'frontend.views.settings.receive_employee_sms', ['ru' => 'Получать SMS, если запрос на сотрудничество направлен на почту не мне, а моему работнику']), 'name' => 'Sms[' . $user->id . '][receive_employee_sms]']);
                    } ?>
                </div>
            </div>
            <hr style="height: 2px;">
            <?php } ?>
        </div>

        <div class="box-footer clearfix">
            <?= Html::submitButton('<i class="icon fa fa-save"></i> ' . Yii::t('message', 'frontend.views.settings.save_two', ['ru' => 'Сохранить']) . ' ', ['class' => 'btn btn-success']) ?>
        </div>
        <?php
        ActiveForm::end();
        ?>
    </div>
</section>
