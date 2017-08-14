<?php

namespace frontend\modules\clientintegr\modules\rkws\controllers;

use Yii;
use yii\web\Controller;
use api\common\models\RkAgent;
use api\common\models\RkAgentSearch;
use frontend\modules\clientintegr\modules\rkws\components\ApiHelper;
use yii\data\ActiveDataProvider;
// use yii\mongosoft\soapserver\Action;

/**
 * Description of SiteController
 * F-Keeper SOAP server based on mongosoft\soapserver
 * Author: R.Smirnov
 */

class EdismController extends \frontend\modules\clientintegr\controllers\DefaultController {
    
        
    public function actionIndex() {
        
        $searchModel = new \api\common\models\RkEdismSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
                
        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('index',[
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
        } else {
            return $this->render('index',[
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
        }     
        
    }
    
    public function actionView($id)
    {
        return $this->render('view', [
            'dataProvider' => $this->findModel($id),
        ]);
    }
    
    public function actionGetws() {
        
   //  $resres = ApiHelper::getAgents();     
        
        $res = new \frontend\modules\clientintegr\modules\rkws\components\EdismHelper();
        $res->getEdism();
        
            $this->redirect('\clientintegr\rkws\default');
            
    }
      
        protected function findModel($id)
    {
        if (($dmodel = \api\common\models\RkDic::findOne($id)) !== null) {
            
            $model = RkEdism::find()->andWhere('acc = :acc',[':acc' => $dmodel->org_id]);
            
            $dataProvider = new ActiveDataProvider([
                                        'query' => $model,
                                        'sort' => false ]);
            
            return $dataProvider;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

  
   
}
