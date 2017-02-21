<?php

namespace franchise\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\components\AccessRule;

/**
 * Description of FinanceController
 *
 * @author sharaf
 */
class FinanceController extends DefaultController {

    /**
     * Displays finance index
     * 
     * @return mixed
     */
    public function actionIndex() {
        return $this->render("/site/under-construction");
    }
}
