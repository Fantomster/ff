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
            <?= Html::submitButton('Сохранить изменения', ['class' => 'btn btn-success margin-right-15', 'id' => 'saveOrg', 'disabled' => true]) ?>
            <?= Html::button('Отменить изменения', ['class' => 'btn btn-default', 'id' => 'cancelOrg', 'disabled' => true]) ?>
        </div>
        <?php
        ActiveForm::end();
        Pjax::end();
        ?>
    </div>
</section>
