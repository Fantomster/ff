<?php
use yii\widgets\Breadcrumbs;
use yii\helpers\Url;
use yii\widgets\Pjax;
use common\models\Users;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use kartik\widgets\Select2;
use yii\web\JsExpression;

$this->title = Yii::t('message', 'frontend.views.mercury.new_transport_vsd', ['ru'=>'Новый транспортный ВСД ']);
?>
<section class="content-header">
        <h1 class="margin-right-350">
            <i class="fa fa-list-alt"></i> <?= Yii::t('message', 'frontend.views.mercury.new_transport_vsd_create', ['ru'=>'Создание нового транспортного ВСД']) ?>
        </h1>
        <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            [
                'label' => Yii::t('message', 'frontend.views.layouts.client.integration', ['ru'=>'Интеграция']),
                'url' => ['/clientintegr/default'],
            ],
            Yii::t('message', 'frontend.views.mercury.new_transport_vsd_step_two', ['ru'=>'Шаг 2. Создание нового транспортного ВСД'])
        ],
    ])
?>
</section>
<section class="content">
<div class="box box-info">
    <div class="box-body">
            <div class="panel-body">
                <ul class="nav fk-tab nav-tabs  pull-left">
                    <?= '<li class="disabled">'.Html::a(Yii::t('message', 'frontend.views.mercury.new_transport_vsd_select_product', ['ru'=>' Выбор продукции']) . '  <i class="fa fa-fw fa-hand-o-right"></i>',['vendor/step-1'],['class'=>'btn btn-default']).'</li>';?>
                    <?= '<li class="disabled">'.Html::a(Yii::t('message', 'frontend.views.mercury.new_transport_vsd_recipient_info', ['ru'=>' Информация о товарополучателе'])).'</li>'?>
                    <?= '<li class="active">'.Html::a(Yii::t('message', 'frontend.views.mercury.new_transport_vsd_transport_info', ['ru'=>'Информация о транспорте'])).'</li>'?>
                </ul>
                <ul class="fk-prev-next pull-right">
                  <?= '<li class="fk-prev">' . Html::a(Yii::t('message', 'frontend.views.vendor.back', ['ru'=>'Назад']), ['step-2']) . '</li>' ?>
                </ul>
        </div>
        <?php Pjax::begin(['id' => 'pjax-container'])?>
        <div class="panel-body">
            <div class="callout callout-fk-info">
                <h4><?= Yii::t('message', 'frontend.views.vendor.step_three', ['ru'=>'ШАГ 3']) ?></h4>
                <p><?= Yii::t('message', 'frontend.views.mercury.new_transport_vsd_get_транспорт_info', ['ru'=>'Укажите информацию о транспорте']) ?></p>
            </div>
            <?php $form = ActiveForm::begin(['id' => 'StockEntryForm']); ?>
            <?= $form->field($model, 'type',['enableClientValidation' => false])->hiddenInput()->label(false); ?>
            <?= $form->field($model, 'type_name')->textInput(['maxlength' => true]); ?>

            <?= $form->field($model, 'car_number')->textInput(['maxlength' => true]); ?>
            <?= $form->field($model, 'trailer_number')->textInput(['maxlength' => true]); ?>
            <?= $form->field($model, 'container_number')->textInput(['maxlength' => true]); ?>
            <?=
            $form->field($model, 'storage_type')
                ->dropDownList(\api\common\models\merc\MercVsd::$storage_types,['prompt' => 'не указано']);
            ?>
            </div>
            <div class="form-group">
                <?php echo Html::submitButton('Оформить', ['class' => 'btn btn-success']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
        <?php Pjax::end(); ?>
    </div>
</div>
</section>
