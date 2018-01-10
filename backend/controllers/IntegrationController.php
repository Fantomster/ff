<?php

namespace backend\controllers;

use yii\web\Controller;

class IntegrationController extends Controller
{
    public function actionIndex(){
        return $this->render('index');
    }
}