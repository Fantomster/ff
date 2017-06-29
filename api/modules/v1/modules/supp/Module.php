<?php

namespace api\modules\v1\modules\supp;

class Module extends \yii\base\Module
{
    public $urlPrefix = '';
    
    public $urlRules = [
        'supp/wsdl'                => 'supp/default/wsdl',
        
    ];
    
    
    public function init()
    {
        parent::init();

    
    }
}
