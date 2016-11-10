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
<?php
if ($organization->step == common\models\Organization::STEP_SET_INFO) {
    echo yii\bootstrap\Alert::widget([
        'options' => [
            'class' => 'alert-warning fade in',
        ],
        'body' => 'Для того, чтобы продолжить работу с нашей системой, заполните все необходимые поля формы. '
        . '<a class="btn btn-default btn-sm" href="#">Сделаем это!</a>',
    ]);
}
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
                    $form->field($organization, 'name', [
                        'addon' => ['prepend' => ['content' => '<i class="fa fa-users"></i>']]
                    ])
                    ->label(false)
                    ->textInput(['placeholder' => $organization->getAttributeLabel('name')])
            ?>

            <?=
                    $form->field($organization, 'city', [
                        'addon' => ['prepend' => ['content' => '<i class="fa fa-map"></i>']]
                    ])
                    ->label(false)
                    ->textInput(['placeholder' => $organization->getAttributeLabel('city')])
            ?>

            <?=
                    $form->field($organization, 'address', [
                        'addon' => ['prepend' => ['content' => '<i class="fa fa-compass"></i>']]
                    ])
                    ->label(false)
                    ->textInput(['placeholder' => $organization->getAttributeLabel('address')])
            ?>

            <?php /*
                    $form->field($organization, 'zip_code')
                    ->label(false)
                    ->textInput(['placeholder' => $organization->getAttributeLabel('zip_code')])
             */ ?>

            <?=
                    $form->field($organization, 'phone', [
                        'addon' => ['prepend' => ['content' => '<i class="fa fa-phone"></i>']]
                    ])
                    ->widget(\yii\widgets\MaskedInput::className(), ['mask' => '+7 (999) 999 99 99',])
                    ->label(false)
                    ->textInput(['placeholder' => $organization->getAttributeLabel('phone')])
            ?>

            <?=
                    $form->field($organization, 'email', [
                        'addon' => ['prepend' => ['content' => '<i class="fa fa-envelope"></i>']]
                    ])
                    ->label(false)
                    ->textInput(['placeholder' => $organization->getAttributeLabel('email')])
            ?>

            <?=
                    $form->field($organization, 'website', [
                        'addon' => ['prepend' => ['content' => '<i class="fa fa-globe"></i>']]
                    ])
                    ->label(false)
                    ->textInput(['placeholder' => $organization->getAttributeLabel('website')])
            ?>
        </div>
        <div class="box-footer clearfix">
            <?= Html::submitButton('<i class="icon fa fa-save"></i> Сохранить изменения', ['class' => 'btn btn-success margin-right-15', 'id' => 'saveOrg', 'disabled' => true]) ?>
            <?= Html::button('<i class="icon fa fa-ban"></i> Отменить изменения', ['class' => 'btn btn-gray', 'id' => 'cancelOrg', 'disabled' => true]) ?>
        </div>
        <?php
        ActiveForm::end();
        Pjax::end();
        ?>
    </div>
</section>
