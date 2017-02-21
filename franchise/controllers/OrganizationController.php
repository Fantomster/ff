<?php

namespace franchise\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\components\AccessRule;

/**
 * Description of OrganizationController
 *
 * @author sharaf
 */
class OrganizationController extends Controller {

    /**
     * Displays clients list
     * 
     * @return mixed
     */
    public function actionClients() {
        return $this->render("/site/under-construction");
    }

    /**
     * Displays vendors list
     * 
     * @return mixed
     */
    public function actionVendors() {
        return $this->render("/site/under-construction");
    }
}
