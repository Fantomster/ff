<?php

namespace frontend\modules\billing\controllers;

use frontend\modules\billing\helpers\Logger;
use yii\web\Controller, Yii, yii\web\Response;

class PaymentController extends Controller
{
    public function beforeAction($action)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        Logger::log($_REQUEST);
    }
}