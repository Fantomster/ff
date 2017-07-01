<?php

namespace frontend\modules\vendorintegr;

class Module extends \yii\base\Module
{
    public function init()
    {
        parent::init();

        $this->modules = [
            'odinc' => [
                // здесь имеет смысл использовать более лаконичное пространство имен
                'class' => 'frontend\modules\vendorintegr\modules\odinc\Module',
            ],
            
        //    'supp' => [
                // здесь имеет смысл использовать более лаконичное пространство имен
        //        'class' => 'api\modules\v1\modules\supp\Module',
        //    ],
        ];
    }
}
