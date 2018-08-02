<?php

namespace api\modules\v1\modules\telepad;

class Module extends \yii\base\Module
{
    public $urlPrefix = '';
    
    public $urlRules = [
        'v1/telepad/wsdl'                => 'v1/telepad/default/wsdl',
        
    ];
    
    
    public function init()
    {
        parent::init();

        return true;
    }
}
