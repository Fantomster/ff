<?php

namespace common\widgets\phone;

use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Description of PhoneInput
 *
 * @author sharaf
 */
class PhoneInput extends \borales\extensions\phoneInput\PhoneInput{
    public function init()
    {
        parent::init();
        \borales\extensions\phoneInput\PhoneInputAsset::register($this->view);
        $id = ArrayHelper::getValue($this->options, 'id');
        $jsOptions = $this->jsOptions ? Json::encode($this->jsOptions) : "";
        $this->view->registerJs("$('#$id').intlTelInput($jsOptions);");
        $this->view->registerCss(".intl-tel-input {width: 100%}");
    }
}
