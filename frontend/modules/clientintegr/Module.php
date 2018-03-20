<?php

namespace frontend\modules\clientintegr;

class Module extends \yii\base\Module
{
    public function init()
    {
        parent::init();

        $this->modules = [
            'rkws' => [
                // здесь имеет смысл использовать более лаконичное пространство имен
                'class' => 'frontend\modules\clientintegr\modules\rkws\Module',
            ],
            'iiko' => [
                'class' => 'frontend\modules\clientintegr\modules\iiko\Module',
            ],
            'email' => [
                'class' => 'frontend\modules\clientintegr\modules\email\Module',
            ],
/*            
            'supp' => [
                // здесь имеет смысл использовать более лаконичное пространство имен
                'class' => 'api\modules\v1\modules\supp\Module',
            ],
 */ 
        ];
   
    }
}
