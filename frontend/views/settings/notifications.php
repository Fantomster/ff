<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\checkbox\CheckboxX;

$this->title = Yii::t('message', 'frontend.views.settings.notifications_three', ['ru'=>'Уведомления']);

$mercLicense = $user->organization->getMercLicense();
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
        'homeLink' => ['label' => Yii::t('app', 'frontend.views.to_main', ['ru'=>'Главная']), 'url' => '/'],
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
                    <?php
                    $label = '';
                    switch($user->organization->type_id){
                        case \common\models\Organization::TYPE_RESTAURANT:
                            $label = Yii::t('app', 'frontend.views.settings.notifications.note', ['ru'=>'Уведомления по новым откликам на заявку по email']);
                            break;
                        case \common\models\Organization::TYPE_SUPPLIER:
                            $label = Yii::t('app', 'frontend.views.settings.notifications.note_two', ['ru'=>'Уведомления о назначении исполнителем заявки по email']);
                            break;
                    };

                    echo $form->field($emailNotification, 'request_accept')->widget(CheckboxX::classname(), [
                        'autoLabel' => true,
                        'model' => $emailNotification,
                        'attribute' => 'request_accept',
                        'pluginOptions' => [
                            'threeState' => false,
                            'theme' => 'krajee-flatblue',
                            'enclosedLabel' => false,
                            'size' => 'md',
                        ],
                        'labelSettings' => [
                            'label' => $label,
                            'position' => CheckboxX::LABEL_RIGHT,
                            'options' => ['style' => '']
                        ]
                    ])->label(false);
                    ?>

                    <?php if ($user->role_id == \common\models\Role::ROLE_SUPPLIER_MANAGER) {
                        echo $form->field($emailNotification, 'receive_employee_email')->widget(CheckboxX::classname(), [
                            'autoLabel' => true,
                            'model' => $emailNotification,
                            'attribute' => 'receive_employee_email',
                            'pluginOptions' => [
                                'threeState' => false,
                                'theme' => 'krajee-flatblue',
                                'enclosedLabel' => false,
                                'size' => 'md',
                            ],
                            'labelSettings' => [
                                'label' => Yii::t('app', 'frontend.views.settings.notify_receive_email', ['ru' => 'Получать email, если запрос на сотрудничество направлен на почту не мне, а моему работнику']),
                                'position' => CheckboxX::LABEL_RIGHT,
                                'options' => ['style' => '']
                            ]
                        ])->label(false);
                    }
                    ?>
                    <?=
                    $form->field($user, 'subscribe')->widget(CheckboxX::classname(), [
                        'autoLabel' => true,
                        'model' => $user,
                        'attribute' => 'subscribe',
                        'pluginOptions' => [
                            'threeState' => false,
                            'theme' => 'krajee-flatblue',
                            'enclosedLabel' => false,
                            'size' => 'md',
                        ],
                        'labelSettings' => [
                            'label' => Yii::t('app', 'frontend.views.settings.info_mail', ['ru'=>'Информационные рассылки по email']),
                            'position' => CheckboxX::LABEL_RIGHT,
                            'options' => ['style' => '']
                        ]
                    ])->label(false)
                    ?>
                    <?= isset($mercLicense) ?
                    $form->field($emailNotification, 'merc_vsd')->widget(CheckboxX::classname(), [
                        'autoLabel' => true,
                        'model' => $emailNotification,
                        'attribute' => 'merc_vsd',
                        'pluginOptions' => [
                            'threeState' => false,
                            'theme' => 'krajee-flatblue',
                            'enclosedLabel' => false,
                            'size' => 'md',
                        ],
                        'labelSettings' => [
                            'label' => Yii::t('app', 'frontend.views.settings.vsd_notification', ['ru'=>'Рассылки о непогашенных ВСД']),
                            'position' => CheckboxX::LABEL_RIGHT,
                            'options' => ['style' => '']
                        ]
                    ])->label(false) : ''
                    ?>
                    <?= isset($mercLicense) ?
                        $form->field($emailNotification, 'merc_stock_expiry')->widget(CheckboxX::classname(), [
                            'autoLabel' => true,
                            'model' => $emailNotification,
                            'attribute' => 'merc_stock_expiry',
                            'pluginOptions' => [
                                'threeState' => false,
                                'theme' => 'krajee-flatblue',
                                'enclosedLabel' => false,
                                'size' => 'md',
                            ],
                            'labelSettings' => [
                                'label' => Yii::t('app', 'frontend.views.settings.stock_expiry_notification', ['ru'=>'Рассылки о проблемной продукции']),
                                'position' => CheckboxX::LABEL_RIGHT,
                                'options' => ['style' => '']
                            ]
                        ])->label(false) : ''
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
                    <?php
                    $label = '';
                    switch($user->organization->type_id){
                        case \common\models\Organization::TYPE_RESTAURANT:
                            $label = Yii::t('app', 'frontend.views.settings.notifications.note_three', ['ru'=>'Уведомления по новым откликам на заявку по sms']);
                            break;
                        case \common\models\Organization::TYPE_SUPPLIER:
                            $label = Yii::t('app', 'frontend.views.settings.notifications.note_four', ['ru'=>'Уведомления о назначении исполнителем заявки по sms']);
                            break;
                    };

                    echo $form->field($smsNotification, 'request_accept')->widget(CheckboxX::classname(), [
                        'autoLabel' => true,
                        'model' => $smsNotification,
                        'attribute' => 'request_accept',
                        'pluginOptions' => [
                            'threeState' => false,
                            'theme' => 'krajee-flatblue',
                            'enclosedLabel' => false,
                            'size' => 'md',
                        ],
                        'labelSettings' => [
                            'label' => $label,
                            'position' => CheckboxX::LABEL_RIGHT,
                            'options' => ['style' => '']
                        ]
                    ])->label(false);
                    ?>

                    <?=
                    $form->field($user, 'sms_subscribe')->widget(CheckboxX::classname(), [
                        'autoLabel' => true,
                        'model' => $user,
                        'attribute' => 'sms_subscribe',
                        'pluginOptions' => [
                            'threeState' => false,
                            'theme' => 'krajee-flatblue',
                            'enclosedLabel' => false,
                            'size' => 'md',
                        ],
                        'labelSettings' => [
                            'label' => Yii::t('message', 'frontend.views.settings.notifications.allow_sms', ['ru'=>'Информационные рассылки по sms']),
                            'position' => CheckboxX::LABEL_RIGHT,
                            'options' => ['style' => '']
                        ]
                    ])->label(false)
                    ?>

                    <?php if ($user->role_id == \common\models\Role::ROLE_SUPPLIER_MANAGER) {
                        echo $form->field($smsNotification, 'receive_employee_sms')->widget(CheckboxX::classname(), [
                            'autoLabel' => true,
                            'model' => $smsNotification,
                            'attribute' => 'receive_employee_sms',
                            'pluginOptions' => [
                                'threeState' => false,
                                'theme' => 'krajee-flatblue',
                                'enclosedLabel' => false,
                                'size' => 'md',
                            ],
                            'labelSettings' => [
                                'label' => Yii::t('app', 'frontend.views.settings.receive_employee_sms', ['ru' => 'Получать SMS, если запрос на сотрудничество направлен на почту не мне, а моему работнику']),
                                'position' => CheckboxX::LABEL_RIGHT,
                                'options' => ['style' => '']
                            ]
                        ])->label(false);
                    }
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
                <div class="panel-info settings">
                    <div class="col-md-10" ><i><br><p><?= Yii::t('app', 'frontend.views.settings.if_you_wanna', ['ru'=>'Если вы хотите добавить еще один email для получения уведомлений без заведения нового сотрудника, то вы можете сделать это в таблице ниже.']) ?><br>
                            <?= Yii::t('app', 'frontend.views.settings.if_you_wanna_two', ['ru'=>'Для каждого добавленного email вы можете выбрать события, о которых будут приходить уведомления.']) ?></p><br></div></i></div>
                </div>
                <div class="col-md-6">
                    <?=$this->render('_additional_email', ['additional_email' => $additional_email, 'user' => $user, 'mercLicense' => $mercLicense])?>
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
