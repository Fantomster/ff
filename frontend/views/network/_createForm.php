<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
?>
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true" style="padding-bottom: 10px;">×</button>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="data-modal__logo"><img src="images/tmp_file/logo.png" alt=""></div><br>
            <?php
            $form = ActiveForm::begin([
                        'id' => 'create-network-form',
                        'enableAjaxValidation' => true,
                        'enableClientValidation' => false,
                        'validateOnSubmit' => true,
                        'action' => Url::to('/network/create'),
            ]);
            ?>
            <?=
                    $form->field($organization, 'type_id')
                    ->radioList(
                            [\common\models\Organization::TYPE_RESTAURANT => ' Закупщик', \common\models\Organization::TYPE_SUPPLIER => ' Поставщик'], 
                            [
                                'item' => function($index, $label, $name, $checked, $value) use ($organization) {

                                    $checked = $checked ? 'checked' : '';
                                    $return = '<label>';
                                    $return .= '<input type="radio" name="' . $name . '" value="' . $value . '" '.$checked.'>';
                                    $return .= '<i class="radio-ico"></i><span>' . $label . '</span>';
                                    $return .= '</label>';

                                    return $return;
                                }
                            ]
                    )
                    ->label(false);
            ?>
            <hr>
            <?=
                    $form->field($organization, 'name')
                    ->label(false)
                    ->textInput(['class' => 'form-control', 'placeholder' => 'Название организации']);
            ?>
            </div>
            <button type="submit" class="but btn-success create-network">Создать</button>
            <?php ActiveForm::end(); ?>
        </div>     
    </div>
</div>