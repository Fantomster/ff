<?php

namespace api\controllers;

use Yii;
use yii\web\Controller;

/**
 * Description of DefaultController
 *
 */
class DefaultController extends Controller {

    // Default test page
    
    public function actionIndex() {
        
        $searchModel = new BuisinessInfoSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }
    
    
}
