<?php

namespace api\modules\v1;

class Module extends \yii\base\Module
{
    public function init()
    {
        parent::init();

        $this->modules = [
            'restor' => [
                // здесь имеет смысл использовать более лаконичное пространство имен
                'class' => 'api\modules\v1\modules\restor\Module',
            ],
            

            'supp' => [
                // здесь имеет смысл использовать более лаконичное пространство имен
                'class' => 'api\modules\v1\modules\supp\Module',
            ],

            'odinsrest' => [
                // здесь имеет смысл использовать более лаконичное пространство имен
                'class' => 'api\modules\v1\modules\odinsrest\Module',
            ],

            'telepad' => [
                // здесь имеет смысл использовать более лаконичное пространство имен
                'class' => 'api\modules\v1\modules\telepad\Module',
            ],

            'mobile' => [
                // здесь имеет смысл использовать более лаконичное пространство имен
                'class' => 'api\modules\v1\modules\mobile\Module',
            ]
        ];
    
    }
}
