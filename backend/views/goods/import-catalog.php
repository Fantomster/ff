<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\widgets\FileInput;
use yii\widgets\ActiveForm;
?>
<section>
    <?php if ($relation->uploaded_processed) { ?>
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-success" role="alert">
                    Каталог успешно импортирован!
                </div>
            </div>
        </div>
    <?php } else { ?>
        <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]);
        ?>
        <?php if (Yii::$app->session->hasFlash('fail')): ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-danger" role="alert">
                        <?= Yii::$app->session->getFlash('fail') ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <div class="row">
            <div class="col-md-2">
                <?=
                Html::a(
                        'Скачать шаблон (XLS)', Url::to('@web/upload/template.xlsx'), ['class' => 'btn btn-default', 'style' => ['margin-right' => '10px;']]
                )
                ?>
            </div><div class="col-md-10"><?=
                Html::a(
                        'Скачать каталог', $relation->getUploadUrl('uploaded_catalog'), ['class' => 'btn btn-default', 'style' => ['margin-right' => '10px;']]
                )
                ?>
            </div>
        </div><div class="row">
            <div class="col-md-6" style="padding-top: 10px;">
                <?=
                FileInput::widget([
                    'model' => $importModel,
                    'attribute' => 'importFile',
                    'pluginOptions' => [
                        'showPreview' => false,
                        'showCaption' => true,
                        'showRemove' => true,
                        'showUpload' => true,
                        'removeLabel' => '',
                        'browseLabel' => 'Загрузить...',
                        'uploadLabel' => 'Импортировать',
                    ],
                ]);
                ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    <?php } ?>
</section>