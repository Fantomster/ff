<?php

namespace frontend\modules\clientintegr\modules\rkws\controllers;

use Yii;
use yii\web\Controller;
use api\common\models\RkCategory;
use frontend\modules\clientintegr\modules\rkws\components\ApiHelper;
use yii\data\ActiveDataProvider;
// use yii\mongosoft\soapserver\Action;

/**
 * Description of SiteController
 * F-Keeper SOAP server based on mongosoft\soapserver
 * Author: R.Smirnov
 */

class ProductgroupController extends \frontend\modules\clientintegr\controllers\DefaultController {
    
        
    public function actionIndex() {
        
        $searchModel = new RkCategory();
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
        update('rk_category', ['disabled' => '1'], 'acc='.Yii::$app->user->identity->organization_id.' and active = 1')->execute();

        return $this->render('view', [
            'dataProvider' => $this->findModel($id),
        ]);
    }
    
    public function actionGetws() {
        
   //  $resres = ApiHelper::getAgents();     
        
        $res = new \frontend\modules\clientintegr\modules\rkws\components\ProductgroupHelper();
        $res->getCategory();
        
            $this->redirect('\clientintegr\rkws\default');
            
    }
      
        protected function findModel($id)
    {
        if (($dmodel = \api\common\models\RkDic::findOne($id)) !== null) {
            
            $model = RkCategory::find()->andWhere('acc = :acc',[':acc' => $dmodel->org_id]);
            
            $dataProvider = new ActiveDataProvider([
                                        'query' => $model,
                                        'sort' => false ]);
            
            return $dataProvider;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

  
   
}
