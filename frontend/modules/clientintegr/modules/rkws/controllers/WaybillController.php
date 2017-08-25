<?php

namespace frontend\modules\clientintegr\modules\rkws\controllers;

use Yii;
use yii\web\Controller;
use api\common\models\RkWaybill;
use api\common\models\RkAgentSearch;
use frontend\modules\clientintegr\modules\rkws\components\ApiHelper;
use api\common\models\RkWaybilldata;
use yii\data\ActiveDataProvider;
use common\models\User;
use yii\helpers\ArrayHelper;
use kartik\grid\EditableColumnAction;

// use yii\mongosoft\soapserver\Action;

/**
 * Description of SiteController
 * F-Keeper SOAP server based on mongosoft\soapserver
 * Author: R.Smirnov
 */
class WaybillController extends \frontend\modules\clientintegr\controllers\DefaultController {

    public function actions() {
        return ArrayHelper::merge(parent::actions(), [
                    'edit' => [// identifier for your editable action
                        'class' => EditableColumnAction::className(), // action class name
                        'modelClass' => RkWaybilldata::className(), // the update model class
                        'outputValue' => function ($model, $attribute, $key, $index) {
                            $value = $model->$attribute;                 // your attribute value
                            if ($attribute === 'pdenom') {

                                if (!is_numeric($model->pdenom))
                                    return '';

                                $rkProd = \api\common\models\RkProduct::findOne(['id' => $value]);
                                $model->product_rid = $rkProd->id;
                                $model->munit_rid = $rkProd->unit_rid;
                                $model->save(false);
                                return $rkProd->denom;       // return formatted value if desired
                            }
                            return '';                                   // empty is same as $value
                        },
                        'outputMessage' => function($model, $attribute, $key, $index) {
                            return '';                                  // any custom error after model save
                        },
                    // 'showModelErrors' => true,                     // show model errors after save
                    // 'errorOptions' => ['header' => '']             // error summary HTML options
                    // 'postOnly' => true,
                    // 'ajaxOnly' => true,
                    // 'findModel' => function($id, $action) {},
                    // 'checkAccess' => function($action, $model) {}
                    ],
                    'changekoef' => [// identifier for your editable column action
                        'class' => EditableColumnAction::className(), // action class name
                        'modelClass' => RkWaybilldata::className(), // the model for the record being edited
                        //   'outputFormat' => ['decimal', 6],    
                        'outputValue' => function ($model, $attribute, $key, $index) {
                            if ($attribute === 'vat') {
                                return $model->$attribute / 100;
                            } else {
                                return round($model->$attribute, 6);      // return any custom output value if desired    
                            }
                            //       return $model->$attribute;
                        },
                        'outputMessage' => function($model, $attribute, $key, $index) {
                            return '';                                  // any custom error to return after model save
                        },
                        'showModelErrors' => true, // show model validation errors after save
                        'errorOptions' => ['header' => '']                // error summary HTML options
                    // 'postOnly' => true,
                    // 'ajaxOnly' => true,
                    // 'findModel' => function($id, $action) {},
                    // 'checkAccess' => function($action, $model) {}
                    ]
        ]);
    }

    public function actionIndex() {

        $searchModel = new \common\models\search\OrderSearch();

        $dataProvider = $searchModel->searchWaybill(Yii::$app->request->queryParams);
        
        $lic = $this->checkLic();       
        
        $vi = $lic ? 'index' : '/default/_nolic';

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial($vi, [
                        'searchModel' => $searchModel,
                        'dataProvider' => $dataProvider,
                        'lic' => $lic,
            ]);
        } else {
            return $this->render($vi, [
                        'searchModel' => $searchModel,
                        'dataProvider' => $dataProvider,
                        'lic' => $lic,
            ]);
        }
    }

    public function actionMap($waybill_id) {

        $records = RkWaybilldata::find()->select('rk_waybill_data.*, rk_product.denom as pdenom ')->andWhere(['waybill_id' => $waybill_id])->leftJoin('rk_product', 'rk_product.id = product_rid');

        $dataProvider = new ActiveDataProvider(['query' => $records,
            'sort' => false,
        ]);
        
        $lic = $this->checkLic();       
        $vi = $lic ? 'indexmap' : '/default/_nolic';

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial($vi, [
                        'dataProvider' => $dataProvider,
            ]);
        } else {
            return $this->render($vi, [
                        'dataProvider' => $dataProvider,
            ]);
        }
    }

    public function actionCleardata($id) {

        $model = $this->findDataModel($id);

        $model->quant = $model->defquant;
        $model->sum = $model->defsum;
        $model->koef = 1;

        if (!$model->save()) {
            echo $model->getErrors();
            die();
        }

        return $this->redirect(['map', 'waybill_id' => $model->waybill->id]);
    }

    public function actionAutocomplete($term = null) {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        // $out = ['results' => ['id' => '0', 'text' => 'Создать контрагента']];
        if (!is_null($term)) {
            $query = new \yii\db\Query;

            // $query->select("`id`, CONCAT(`inn`,`denom`) AS `text`")
            $query->select(['id' => 'id', 'text' => 'CONCAT(`denom`," (",unitname,")")'])
                    ->from('rk_product')
                    ->where('acc = :acc', [':acc' => User::findOne(Yii::$app->user->id)->organization_id])
                    ->andwhere("denom like :denom ", [':denom' => '%' . $term . '%'])
                    ->limit(20);

            $command = $query->createCommand();
            $command->db = Yii::$app->db_api;
            $data = $command->queryAll();
            $out['results'] = array_values($data);
        }
        //  $out['results'][] = ['id' => '0', 'text' => 'Создать контрагента'];
        return $out;
    }

    public function actionUpdate($id) {
        $model = $this->findModel($id);
        
        $lic = $this->checkLic();       
        $vi = $lic ? 'update' : '/default/_nolic';

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            //  return $this->redirect(['view', 'id' => $model->id]);

            if ($model->getErrors()) {
                var_dump($model->getErrors());
                exit;
            }

            return $this->redirect(['index']);
        } else {
            return $this->render($vi, [
                        'model' => $model,
            ]);
        }
    }

    public function actionCreate($order_id) {
        
        $ord = \common\models\Order::findOne(['id' => $order_id]);
        
        if (!$ord) {
            echo "Can't find order";
            die();
        }
                
        $model = new RkWaybill();
        $model->order_id = $order_id;
        $model->status_id = 1;
        $model->org = $ord->client_id;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            if ($model->getErrors()) {
                var_dump($model->getErrors());
                exit;
            }

            return $this->redirect(['index']);
        } else {
            return $this->render('create', [
                        'model' => $model,
            ]);
        }
    }

    public function actionSendws($waybill_id) {

        //  $resres = ApiHelper::getAgents();     

        $res = new \frontend\modules\clientintegr\modules\rkws\components\WaybillHelper();
        $res->sendWaybill($waybill_id);

        $this->redirect('\clientintegr\rkws\waybill\index');
    }
    
    protected function checkLic() {
     
    $lic = \api\common\models\RkService::find()->andWhere('org = :org',['org' => Yii::$app->user->identity->organization_id])->one(); 
    $t = strtotime(date('Y-m-d H:i:s',time()));
    
    if ($lic) {
       if ($t >= strtotime($lic->fd) && $t<= strtotime($lic->td) && $lic->status_id === 2 ) { 
       $res = $lic; 
    } else { 
       $res = 0; 
    }
    } else 
       $res = 0; 
    
    
    return $res ? $res : null;
        
    }

    protected function findModel($id) {
        if (($model = RkWaybill::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    protected function findDataModel($id) {
        if (($model = RkWaybilldata::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
