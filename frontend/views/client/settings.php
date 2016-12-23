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
        Pjax::begin(['enablePushState' => false, 'id' => 'settingsInfo', 'timeout' => 5000]);
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
            <div class="row">

                <div class="col-md-10">
                    <fieldset>
                        <legend>Данные организации:</legend>
                        <div class="row">

                            <div class="col-md-6">
                                <div class="form-group">
                                    <?=
                                            $form->field($organization, 'name', [
                                                'addon' => ['prepend' => ['content' => '<i class="fa fa-users"></i>']]
                                            ])
                                            ->label('Название ресторана <span style="font-size:12px; color: #dd4b39;"><i class="fa fa-fw fa-asterisk"></i></span>')
                                            ->textInput(['placeholder' => 'Введите название ресторана'])
                                    ?>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <?=
                                            $form->field($organization, 'name', [
                                                'addon' => ['prepend' => ['content' => '<i class="fa fa-users"></i>']]
                                            ])
                                            ->label('Название юридического лица <span style="font-size:12px; color: #dd4b39;"><i class="fa fa-fw fa-asterisk"></i></span>')
                                            ->textInput(['placeholder' => 'Введите название юридического лица'])
                                    ?>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <?=
                                        $form->field($organization, 'city', [
                                            'addon' => ['prepend' => ['content' => '<i class="fa fa-map"></i>']]
                                        ])
                                        ->label('Город')
                                        ->textInput(['placeholder' => 'Введите ваш город'])
                                ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <?=
                                        $form->field($organization, 'address', [
                                            'addon' => ['prepend' => ['content' => '<i class="fa fa-compass"></i>']]
                                        ])
                                        ->label('Адрес')
                                        ->textInput(['placeholder' => 'Введите ваш адрес'])
                                ?>                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <?=
                                        $form->field($organization, 'website', [
                                            'addon' => ['prepend' => ['content' => '<i class="fa fa-globe"></i>']]
                                        ])
                                        ->label('Веб-сайт')
                                        ->textInput(['placeholder' => 'Введите адрес вашего веб-сайта'])
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">

                    <img id="newAvatar" style="background-color:#ccc; display: block; width: 100%; /*max-height:210px; max-width: 210px;*/ z-index: 1; margin-top: 15px;
                         " src="images/rest-noavatar.gif">

                    <a href="#" class="btn btn-gray" style="width:100%; display: inline-block; z-index: 999; margin-top:-15px; margin-bottom:20px;"> Загрузить аватар</a>
                </div>
            </div>            
            <fieldset>
                <legend>Контактное лицо:</legend>
                <div class="row">

                    <div class="col-md-4">
                        <div class="form-group">
                            <?=
                                    $form->field($organization, 'name', [
                                        'addon' => ['prepend' => ['content' => '<i class="fa fa-users"></i>']]
                                    ])
                                    ->label('ФИО контактного лица')
                                    ->textInput(['placeholder' => 'Введите ФИО контактного лица'])
                            ?>                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?=
                                    $form->field($organization, 'email', [
                                        'addon' => ['prepend' => ['content' => '<i class="fa fa-envelope"></i>']]
                                    ])
                                    ->label('E-mail')
                                    ->textInput(['placeholder' => "Введите E-mail"])
                            ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?=
                    $form->field($organization, 'phone', [
                        'addon' => ['prepend' => ['content' => '<i class="fa fa-phone"></i>']]
                    ])
                    ->widget(\yii\widgets\MaskedInput::className(), ['mask' => '+7 (999) 999 99 99',])
                    ->label('Телефон')
                    ->textInput()
            ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Информация об организации</label>
                            <textarea class="form-control" rows="3" placeholder="Несколько слов об организации ..."></textarea>
                        </div>
                    </div>
                </div>
            </fieldset>
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
