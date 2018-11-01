<?php

namespace api_web\modules\integration;

class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'api_web\modules\integration\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        $this->modules = [
            'iiko' => [
                'class' => 'api_web\modules\integration\modules\iiko\Module',
            ],
            /*
            'rkeeper' => [
                'class' => 'api_web\modules\integration\modules\rkeeper\Module',
            ],
            'one_s' => [
                'class' => 'api_web\modules\integration\modules\one_s\Module',
            ],*/
            'vetis' => [
                'class' => 'api_web\modules\integration\modules\vetis\Module',
            ],
            'egais' => [
                'class' => 'api_web\modules\integration\modules\egais\Module',
            ],
        ];
    }
}
