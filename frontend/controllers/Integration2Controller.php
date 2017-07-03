<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use common\models\Organization;

/**
 * Description of DefaultController
 *
 */
class IntegrationController extends DefaultController {
    public function actionIndex() {
        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('index');
        } else {
            return $this->render('index');
        }
    }
    
}
