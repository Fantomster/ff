<?php

namespace frontend\modules\clientintegr\modules\email;

use yii\web\View;

class Module extends \yii\base\Module
{

    public function init()
    {
        parent::init();
    }

    public function renderMenu()
    {
        return (new View())->renderFile(realpath($this->basePath . '/views/_menu.php'));
    }
}
