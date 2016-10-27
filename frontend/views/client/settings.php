<?php
use yii\widgets\Breadcrumbs;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use yii\widgets\Pjax;

$this->registerJs(
        '$("document").ready(function(){
            $(".settings").on("click", "#cancelOrg", function() {
                $.pjax.reload({container: "#settingsInfo"});            
            });
            $(".settings").on("change paste keyup", ".form-control", function() {
                $("#cancelOrg").prop( "disabled", false );
                $("#saveOrg").prop( "disabled", false );
            });
        });'
);
?>
<!--<div style="padding: 20px 30px; background: rgb(243, 156, 18); z-index: 999999; font-size: 16px; font-weight: 600;"><a class="pull-right" href="#" data-toggle="tooltip" data-placement="left" title="Never show me this again!" style="color: rgb(255, 255, 255); font-size: 20px;">×</a><a href="https://themequarry.com" style="color: rgba(255, 255, 255, 0.901961); display: inline-block; margin-right: 10px; text-decoration: none;">Ready to sell your theme? Submit your theme to our new marketplace now and let over 200k visitors see it!</a><a class="btn btn-default btn-sm" href="https://themequarry.com" style="margin-top: -5px; border: 0px; box-shadow: none; color: rgb(243, 156, 18); font-weight: 600; background: rgb(255, 255, 255);">Let's Do It!</a></div>-->
<?=
yii\bootstrap\Alert::widget([
    'options' => [
        'class' => 'alert-warning',
    ],
    'body' => 'Для того, чтобы продолжить работу с нашей системой, заполните все необходимые поля формы. '
    . '<a class="btn btn-default btn-sm" href="#">Сделаем это!</a>',
]);
?>
<section class="content-header">
    <h1>
        <i class="fa fa-gears"></i> Общие
        <small>Информация об организации</small>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            'Настройки',
            'Общие',
        ],
    ])
    ?>
</section>
<section class="content">
    <div class="box box-info settings">
        <?php
        Pjax::begin(['enablePushState' => false, 'id' => 'settingsInfo', 'timeout' => 3000]);
        $form = ActiveForm::begin([
                    'id' => 'generalSettings',
                    'enableAjaxValidation' => false,
                    'options' => [
                        'data-pjax' => true,
                    ],
                    'method' => 'get',
        ]);
        ?>
        <div class="box-body">

            <?=
                    $form->field($organization, 'name')
                    ->label(false)
                    ->textInput(['placeholder' => $organization->getAttributeLabel('name')])
            ?>

            <?=
                    $form->field($organization, 'city')
                    ->label(false)
                    ->textInput(['placeholder' => $organization->getAttributeLabel('city')])
            ?>

            <?=
                    $form->field($organization, 'address')
                    ->label(false)
                    ->textInput(['placeholder' => $organization->getAttributeLabel('address')])
            ?>

            <?=
                    $form->field($organization, 'zip_code')
                    ->label(false)
                    ->textInput(['placeholder' => $organization->getAttributeLabel('zip_code')])
            ?>

            <?=
                    $form->field($organization, 'phone', [
                        'addon' => ['prepend' => ['content' => '<i class="fa fa-phone"></i>']]
                    ])
                    ->widget(\yii\widgets\MaskedInput::className(), ['mask' => '+7 (999) 999 99 99',])
                    ->label(false)
                    ->textInput(['placeholder' => $organization->getAttributeLabel('phone')])
            ?>

            <?=
                    $form->field($organization, 'email')
                    ->label(false)
                    ->textInput(['placeholder' => $organization->getAttributeLabel('email')])
            ?>

            <?=
                    $form->field($organization, 'website')
                    ->label(false)
                    ->textInput(['placeholder' => $organization->getAttributeLabel('website')])
            ?>
        </div>
        <div class="box-footer clearfix">
            <?= Html::submitButton('<i class="icon fa fa-save"></i> Сохранить изменения', ['class' => 'btn btn-success margin-right-15', 'id' => 'saveOrg', 'disabled' => true]) ?>
            <?= Html::button('<i class="icon fa fa-ban"></i> Отменить изменения', ['class' => 'btn btn-default', 'id' => 'cancelOrg', 'disabled' => true]) ?>
        </div>
        <?php
        ActiveForm::end();
        Pjax::end();
        ?>
    </div>
</section>
