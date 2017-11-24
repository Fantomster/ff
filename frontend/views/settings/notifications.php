<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\checkbox\CheckboxX;

$this->title = Yii::t('message', 'frontend.views.settings.notifications_three', ['ru'=>'Уведомления']);
?>
<section class="content-header">
    <h1>
        <i class="fa fa-gears"></i> <?= Yii::t('message', 'frontend.views.settings.notifications', ['ru'=>'Уведомления']) ?>
        <small><?= Yii::t('message', 'frontend.views.settings.notifications_settings', ['ru'=>'Настройка уведомлений']) ?></small>
    </h1>
    <?=
    yii\widgets\Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            Yii::t('message', 'frontend.views.settings.settings_two', ['ru'=>'Настройки']),
            Yii::t('message', 'frontend.views.settings.notifications_two', ['ru'=>'Уведомления']),
        ],
    ])
    ?>
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
            <div class="row">
                <div class="col-md-6">
                    <?=
                    $form->field($emailNotification, 'order_created')->widget(CheckboxX::classname(), [
                        'autoLabel' => true,
                        'model' => $emailNotification,
                        'attribute' => 'order_created',
                        'pluginOptions' => [
                            'threeState' => false,
                            'theme' => 'krajee-flatblue',
                            'enclosedLabel' => false,
                            'size' => 'md',
                        ],
                        'labelSettings' => [
                            'label' => Yii::t('message', 'frontend.views.settings.new_order', ['ru'=>'Уведомление о новом заказе по email']),
                            'position' => CheckboxX::LABEL_RIGHT,
                            'options' => ['style' => '']
                        ]
                    ])->label(false)
                    ?>
                    <?=
                    $form->field($emailNotification, 'order_canceled')->widget(CheckboxX::classname(), [
                        'autoLabel' => true,
                        'model' => $emailNotification,
                        'attribute' => 'order_canceled',
                        'pluginOptions' => [
                            'threeState' => false,
                            'theme' => 'krajee-flatblue',
                            'enclosedLabel' => false,
                            'size' => 'md',
                        ],
                        'labelSettings' => [
                            'label' => Yii::t('message', 'frontend.views.settings.order_cancel_notify', ['ru'=>'Уведомление об отмене заказа по email']),
                            'position' => CheckboxX::LABEL_RIGHT,
                            'options' => ['style' => '']
                        ]
                    ])->label(false)
                    ?>
                    <?=
                    $form->field($emailNotification, 'order_changed')->widget(CheckboxX::classname(), [
                        'autoLabel' => true,
                        'model' => $emailNotification,
                        'attribute' => 'order_changed',
                        'pluginOptions' => [
                            'threeState' => false,
                            'theme' => 'krajee-flatblue',
                            'enclosedLabel' => false,
                            'size' => 'md',
                        ],
                        'labelSettings' => [
                            'label' => Yii::t('message', 'frontend.views.settings.notify_changes', ['ru'=>'Уведомление об изменениях в заказе по email']),
                            'position' => CheckboxX::LABEL_RIGHT,
                            'options' => ['style' => '']
                        ]
                    ])->label(false)
                    ?>
                    <?=
                    $form->field($emailNotification, 'order_processing')->widget(CheckboxX::classname(), [
                        'autoLabel' => true,
                        'model' => $emailNotification,
                        'attribute' => 'order_processing',
                        'pluginOptions' => [
                            'threeState' => false,
                            'theme' => 'krajee-flatblue',
                            'enclosedLabel' => false,
                            'size' => 'md',
                        ],
                        'labelSettings' => [
                            'label' => Yii::t('message', 'frontend.views.settings.notify_begin', ['ru'=>'Уведомление о начале выполнения заказа по email']),
                            'position' => CheckboxX::LABEL_RIGHT,
                            'options' => ['style' => '']
                        ]
                    ])->label(false)
                    ?>
                    <?=
                    $form->field($emailNotification, 'order_done')->widget(CheckboxX::classname(), [
                        'autoLabel' => true,
                        'model' => $emailNotification,
                        'attribute' => 'order_done',
                        'pluginOptions' => [
                            'threeState' => false,
                            'theme' => 'krajee-flatblue',
                            'enclosedLabel' => false,
                            'size' => 'md',
                        ],
                        'labelSettings' => [
                            'label' => Yii::t('message', 'frontend.views.settings.notify_end', ['ru'=>'Уведомление о завершении заказа по email']),
                            'position' => CheckboxX::LABEL_RIGHT,
                            'options' => ['style' => '']
                        ]
                    ])->label(false)
                    ?>
                    <?= ''
//                    $form->field($emailNotification, 'requests')->widget(CheckboxX::classname(), [
//                        'autoLabel' => true,
//                        'model' => $emailNotification,
//                        'attribute' => 'requests',
//                        'pluginOptions' => [
//                            'threeState' => false,
//                            'theme' => 'krajee-flatblue',
//                            'enclosedLabel' => false,
//                            'size' => 'md',
//                        ],
//                        'labelSettings' => [
//                            'label' => 'Информация о заявках по email',
//                            'position' => CheckboxX::LABEL_RIGHT,
//                            'options' => ['style' => '']
//                        ]
//                    ])->label(false)
                    ?>
                </div>
                <div class="col-md-6">
                    <?=
                    $form->field($smsNotification, 'order_created')->widget(CheckboxX::classname(), [
                        'autoLabel' => true,
                        'model' => $smsNotification,
                        'attribute' => 'order_created',
                        'pluginOptions' => [
                            'threeState' => false,
                            'theme' => 'krajee-flatblue',
                            'enclosedLabel' => false,
                            'size' => 'md',
                        ],
                        'labelSettings' => [
                            'label' => Yii::t('message', 'frontend.views.settings.sms_notify', ['ru'=>'Уведомление о новом заказе по sms']),
                            'position' => CheckboxX::LABEL_RIGHT,
                            'options' => ['style' => '']
                        ]
                    ])->label(false)
                    ?>
                    <?=
                    $form->field($smsNotification, 'order_canceled')->widget(CheckboxX::classname(), [
                        'autoLabel' => true,
                        'model' => $smsNotification,
                        'attribute' => 'order_canceled',
                        'pluginOptions' => [
                            'threeState' => false,
                            'theme' => 'krajee-flatblue',
                            'enclosedLabel' => false,
                            'size' => 'md',
                        ],
                        'labelSettings' => [
                            'label' => Yii::t('message', 'frontend.views.settings.sms_cancel_order', ['ru'=>'Уведомление об отмене заказа по sms']),
                            'position' => CheckboxX::LABEL_RIGHT,
                            'options' => ['style' => '']
                        ]
                    ])->label(false)
                    ?>
                    <?=
                    $form->field($smsNotification, 'order_changed')->widget(CheckboxX::classname(), [
                        'autoLabel' => true,
                        'model' => $smsNotification,
                        'attribute' => 'order_changed',
                        'pluginOptions' => [
                            'threeState' => false,
                            'theme' => 'krajee-flatblue',
                            'enclosedLabel' => false,
                            'size' => 'md',
                        ],
                        'labelSettings' => [
                            'label' => Yii::t('message', 'frontend.views.settings.sms_changes', ['ru'=>'Уведомление об изменениях в заказе по sms']),
                            'position' => CheckboxX::LABEL_RIGHT,
                            'options' => ['style' => '']
                        ]
                    ])->label(false)
                    ?>
                    <?=
                    $form->field($smsNotification, 'order_processing')->widget(CheckboxX::classname(), [
                        'autoLabel' => true,
                        'model' => $smsNotification,
                        'attribute' => 'order_processing',
                        'pluginOptions' => [
                            'threeState' => false,
                            'theme' => 'krajee-flatblue',
                            'enclosedLabel' => false,
                            'size' => 'md',
                        ],
                        'labelSettings' => [
                            'label' => Yii::t('message', 'frontend.views.settings.order_sms_begin', ['ru'=>'Уведомление о начале выполнения заказа по sms']),
                            'position' => CheckboxX::LABEL_RIGHT,
                            'options' => ['style' => '']
                        ]
                    ])->label(false)
                    ?>
                    <?=
                    $form->field($smsNotification, 'order_done')->widget(CheckboxX::classname(), [
                        'autoLabel' => true,
                        'model' => $smsNotification,
                        'attribute' => 'order_done',
                        'pluginOptions' => [
                            'threeState' => false,
                            'theme' => 'krajee-flatblue',
                            'enclosedLabel' => false,
                            'size' => 'md',
                        ],
                        'labelSettings' => [
                            'label' => Yii::t('message', 'frontend.views.settings.order_end_sms', ['ru'=>'Уведомление о завершении заказа по sms']),
                            'position' => CheckboxX::LABEL_RIGHT,
                            'options' => ['style' => '']
                        ]
                    ])->label(false)
                    ?>
                    <?= ''
//                    $form->field($smsNotification, 'requests')->widget(CheckboxX::classname(), [
//                        'autoLabel' => true,
//                        'model' => $smsNotification,
//                        'attribute' => 'requests',
//                        'pluginOptions' => [
//                            'threeState' => false,
//                            'theme' => 'krajee-flatblue',
//                            'enclosedLabel' => false,
//                            'size' => 'md',
//                        ],
//                        'labelSettings' => [
//                            'label' => 'Информация о заявках по sms',
//                            'position' => CheckboxX::LABEL_RIGHT,
//                            'options' => ['style' => '']
//                        ]
//                    ])->label(false)
                    ?>
                </div>
                <div class="col-md-6">
                    <?=$this->render('_additional_email', ['additional_email' => $additional_email])?>
                </div>
            </div>
        </div>
        <div class="box-footer clearfix">
        <?= Html::submitButton('<i class="icon fa fa-save"></i> ' . Yii::t('message', 'frontend.views.settings.save_two', ['ru'=>'Сохранить']) . ' ', ['class' => 'btn btn-success']) ?>
        </div>
<?php
ActiveForm::end();
?>
    </div>
</section>
