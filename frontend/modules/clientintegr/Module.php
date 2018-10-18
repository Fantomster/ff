<?php

namespace frontend\modules\clientintegr;

class Module extends \yii\base\Module
{
    public function init()
    {
        parent::init();

        $this->modules = [
            'rkws'      => [
                // здесь имеет смысл использовать более лаконичное пространство имён
                'class' => 'frontend\modules\clientintegr\modules\rkws\Module',
            ],
            'iiko'      => [
                'class' => 'frontend\modules\clientintegr\modules\iiko\Module',
            ],
            'email'     => [
                'class' => 'frontend\modules\clientintegr\modules\email\Module',
            ],
            'merc'      => [
                'class' => 'frontend\modules\clientintegr\modules\merc\Module',
            ],
            'odinsobsh' => [
                'class' => 'frontend\modules\clientintegr\modules\odinsobsh\Module',
            ],
            'tillypad'   => [
                'class' => 'frontend\modules\clientintegr\modules\tillypad\Module',
            ],
            /*
                        'supp' => [
                            // здесь имеет смысл использовать более лаконичное пространство имён
                            'class' => 'api\modules\v1\modules\supp\Module',
                        ],
             */
        ];

    }
}
