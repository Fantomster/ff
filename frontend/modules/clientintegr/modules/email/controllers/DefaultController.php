<?php

namespace frontend\modules\clientintegr\modules\email\controllers;

use yii\web\Controller;

class DefaultController extends Controller
{
    public function actionIndex()
    {
        $this->redirect('invoice');
    }
}