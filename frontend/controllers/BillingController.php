<?php

namespace frontend\controllers;

class BillingController extends \yii\web\Controller
{
    public function actionPayment()
    {
        file_put_contents('payment.txt', print_r($_REQUEST, 1), FILE_APPEND);
    }

}
