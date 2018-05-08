<?php

namespace api\modules\v1\modules\odinsrest;

class Module extends \yii\base\Module
{
    public $urlPrefix = '';
    
    public $urlRules = [
        'v1/odinsrest/wsdl'                => 'v1/odinsrest/default/wsdl',
        
    ];
    
    
    public function init()
    {
        parent::init();

    
    }
}
