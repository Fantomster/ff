<?php
/**
 * Created by PhpStorm.
 * User: xsupervisor
 * Date: 20.06.2018
 * Time: 18:03
 */

namespace console\controllers;


use yii\console\Controller;
use frontend\modules\clientintegr\modules\rkws\components\ServiceHelper;

class ExtlicenseController extends Controller
{

    public function actionTest() {

        echo "test".PHP_EOL;
        $res = new ServiceHelper();
        $res->getObjects();

        echo "OK".PHP_EOL;

    }
}