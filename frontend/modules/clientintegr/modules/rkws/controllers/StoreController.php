<?php

namespace frontend\modules\clientintegr\modules\rkws\controllers;

use Yii;
use yii\web\Controller;
// use api\common\models\RkAgent;
// use api\common\models\RkAgentSearch;
use frontend\modules\clientintegr\modules\rkws\components\ApiHelper;
use api\common\models\RkStoreSearch;
use api\common\models\RkStore;
use yii\data\ActiveDataProvider;

// use yii\mongosoft\soapserver\Action;

/**
 * Description of SiteController
 * F-Keeper SOAP server based on mongosoft\soapserver
 * Author: R.Smirnov
 */

class StoreController extends\frontend\modules\clientintegr\controllers\DefaultController {
    
        
    public function actionIndex() {
        
        $searchModel = new RkStoreSearch();
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
        yii::$app->db_api->
        createCommand()->
        update('rk_storetree', ['disabled' => '1'], 'acc='.Yii::$app->user->identity->organization_id.' and active = 1')->execute();

        return $this->render('view', [
            'dataProvider' => $this->findModel($id),
        ]);
    }
    
    public function actionGetws() {
        
    $res = new \frontend\modules\clientintegr\modules\rkws\components\StoreHelper();
    $res->getStore();
    
        if($res) {
            $this->redirect('\clientintegr\rkws\default');
        }
            
    }
    
            protected function findModel($id)
    {
        if (($dmodel = \api\common\models\RkDic::findOne($id)) !== null) {
            
            $model = RkStore::find()->andWhere('acc = :acc',[':acc' => $dmodel->org_id]);
            
            $dataProvider = new ActiveDataProvider([
                                        'query' => $model,
                                        'sort' => false ]);
            
            return $dataProvider;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
      
    

  
   
}
