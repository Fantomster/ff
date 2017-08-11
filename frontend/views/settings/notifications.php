<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\checkbox\CheckboxX;

$this->title = 'Уведомления';
?>
<section class="content-header">
    <h1>
        <i class="fa fa-gears"></i> Уведомления
        <small>Настройка уведомлений</small>
    </h1>
    <?=
    yii\widgets\Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            'Настройки',
            'Уведомления',
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
                    $form->field($emailNotification, 'orders')->widget(CheckboxX::classname(), [
                        'autoLabel' => true,
                        'model' => $emailNotification,
                        'attribute' => 'orders',
                        'pluginOptions' => [
                            'threeState' => false,
                            'theme' => 'krajee-flatblue',
                            'enclosedLabel' => false,
                            'size' => 'md',
                        ],
                        'labelSettings' => [
                            'label' => 'Информация о заказах по email',
                            'position' => CheckboxX::LABEL_RIGHT,
                            'options' => ['style' => '']
                        ]
                    ])->label(false)
                    ?>
                    <?=
                    $form->field($emailNotification, 'requests')->widget(CheckboxX::classname(), [
                        'autoLabel' => true,
                        'model' => $emailNotification,
                        'attribute' => 'orders',
                        'pluginOptions' => [
                            'threeState' => false,
                            'theme' => 'krajee-flatblue',
                            'enclosedLabel' => false,
                            'size' => 'md',
                        ],
                        'labelSettings' => [
                            'label' => 'Информация о заявках по email',
                            'position' => CheckboxX::LABEL_RIGHT,
                            'options' => ['style' => '']
                        ]
                    ])->label(false)
                    ?>
                </div>
                <div class="col-md-6">
                    <?=
                    $form->field($smsNotification, 'orders')->widget(CheckboxX::classname(), [
                        'autoLabel' => true,
                        'model' => $smsNotification,
                        'attribute' => 'orders',
                        'pluginOptions' => [
                            'threeState' => false,
                            'theme' => 'krajee-flatblue',
                            'enclosedLabel' => false,
                            'size' => 'md',
                        ],
                        'labelSettings' => [
                            'label' => 'Информация о заказах по sms',
                            'position' => CheckboxX::LABEL_RIGHT,
                            'options' => ['style' => '']
                        ]
                    ])->label(false)
                    ?>
                    <?=
                    $form->field($smsNotification, 'requests')->widget(CheckboxX::classname(), [
                        'autoLabel' => true,
                        'model' => $smsNotification,
                        'attribute' => 'orders',
                        'pluginOptions' => [
                            'threeState' => false,
                            'theme' => 'krajee-flatblue',
                            'enclosedLabel' => false,
                            'size' => 'md',
                        ],
                        'labelSettings' => [
                            'label' => 'Информация о заявках по sms',
                            'position' => CheckboxX::LABEL_RIGHT,
                            'options' => ['style' => '']
                        ]
                    ])->label(false)
                    ?>
                </div>
            </div>
        </div>
        <div class="box-footer clearfix">
        <?= Html::submitButton('<i class="icon fa fa-save"></i> Сохранить', ['class' => 'btn btn-success']) ?>
        </div>
<?php
ActiveForm::end();
?>
    </div>
</section>
