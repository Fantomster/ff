<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

$this->registerJs("
		var uploadCrop = $('#upload-avatar').croppie({
			viewport: {
				width: 90,
				height: 90,
				type: 'square'
			},
                        update: function(){
                            uploadCrop.croppie('result', 'canvas').then(function (resp) {
                                $('#image-crop-result').val(resp);
                            });
                        },
			enableExif: true
		});

    
        ");
?>
<?php if ($flash = Yii::$app->session->getFlash('success')): ?>
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title"><?= $flash ?></h4>
    </div>
    <div class="modal-body">
        <img width="90" height="90" src="<?= $profile->avatarUrl ?>" id="newAvatar" class="img-responsive" style="margin:0 auto;" />
    </div>
    <div class="modal-footer">
        <a href="#" class="btn btn-gray" data-dismiss="modal"><i class="icon fa fa-ban"></i> Закрыть</a>
    </div>

<?php else: ?>
    <?php
//Pjax::begin(['enablePushState' => false, 'timeout' => 5000, 'id' => 'pjaxAvatar']);
    $form = ActiveForm::begin([
                'id' => 'avatarForm',
                'action' => ['/settings/ajax-change-avatar'],
                'options' => [
                    'enctype' => 'multipart/form-data',
                    'class' => 'user-form',
                //'data-pjax' => 1,
                ],
    ]);
    ?>
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title">Установка аватара</h4>
    </div>
    <div class="modal-body">
        <div class="col-1-2">
            <div class="upload-demo-wrap">
                <div id="upload-avatar"></div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <?= $form->field($profile, 'avatar')->fileInput(['accept' => 'image/*', 'id' => 'upload'])->label(false) ?>
        <?= Html::hiddenInput('Profile[avatar]', null, ['id' => 'image-crop-result']) ?>
        <?= Html::button('<i class="icon fa fa-save"></i> Сохранить', ['class' => 'btn btn-success saveAva']) ?>
        <a href="#" class="btn btn-gray" data-dismiss="modal"><i class="icon fa fa-ban"></i> Отмена</a>
    </div>
    <?php
    ActiveForm::end();
//Pjax::end();
endif;
?>
