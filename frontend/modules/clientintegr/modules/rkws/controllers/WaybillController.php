<?php

namespace frontend\modules\clientintegr\modules\rkws\controllers;

use api\common\models\RkAgent;
use api\common\models\RkPconst;
use api\common\models\RkStore;
use api\common\models\rkws\RkWaybilldataSearch;
use api\common\models\VatData;
use common\models\CatalogBaseGoods;
use common\models\OrderContent;
use Yii;
use api\common\models\RkWaybill;
use api\common\models\RkWaybilldata;
use common\models\User;
use yii\helpers\ArrayHelper;
use kartik\grid\EditableColumnAction;
use common\models\Organization;
use common\models\Order;
use yii\helpers\Url;


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
                                $model->linked_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');

                             //   $model->koef = 1.8;
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
        $way = Yii::$app->request->get('way',0);
       //  $page = Yii::$app->request->get('page') ? Yii::$app->request->get('page') : 0;
       //  $perPage = Yii::$app->request->get('per-page') ? Yii::$app->request->get('per-page') : 0;

        Url::remember();

        $searchModel = new \common\models\search\OrderSearch();
        $organization = Organization::findOne(User::findOne(Yii::$app->user->id)->organization_id);

        $today = new \DateTime();
        $searchModel->date_to = $today->format('d.m.Y');
        $searchModel->date_from = Yii::$app->formatter->asTime($this->getEarliestOrder($organization->id), "php:d.m.Y");

        $dataProvider = $searchModel->searchWaybill(Yii::$app->request->queryParams);

      //  $dataProvider->pagination->pageSize=3;

        $lic0 = Organization::getLicenseList();
        //$lic = $this->checkLic();
        $lic = $lic0['rkws'];
        $licucs = $lic0['rkws_ucs'];
        $vi = (($lic) && ($licucs)) ? 'index' : '/default/_nolic';

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial($vi, [
                        'searchModel' => $searchModel,
                        'dataProvider' => $dataProvider,
                        'lic' => $lic,
                'visible' =>RkPconst::getSettingsColumn(Organization::findOne(User::findOne(Yii::$app->user->id)->organization_id)->id),
                        'licucs' => $licucs,
                        'way' => $way,
            ]);
        } else {
            return $this->render($vi, [
                        'searchModel' => $searchModel,
                        'dataProvider' => $dataProvider,
                        'lic' => $lic,
                    'visible' =>RkPconst::getSettingsColumn(Organization::findOne(User::findOne(Yii::$app->user->id)->organization_id)->id),
                        'licucs' => $licucs,
                        'way' => $way,

            ]);
        }
    }


    public function actionMap($waybill_id) {
        
        $wmodel = RkWaybill::find()->andWhere('id= :id',[':id' => $waybill_id])->one();
        $vatData = VatData::getVatList();

        // Используем определение браузера и платформы для лечения бага с клавиатурой Android с помощью USER_AGENT (YT SUP-3)

            $userAgent = \xj\ua\UserAgent::model();
            /* @var \xj\ua\UserAgent $userAgent */

                $platform = $userAgent->platform;
                $browser = $userAgent->browser;

                 if (stristr($platform,'android') OR stristr($browser,'android')) {
                    $isAndroid = true;
                 } else $isAndroid = false;
        
        if(!$wmodel) {
            echo "Cant find wmodel in map controller";
            die();
        }

        $searchModel = new RkWaybilldataSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
    
        $agentModel = RkAgent::findOne(['rid' => $wmodel->corr_rid, 'acc' => $wmodel->org]);
        $storeModel = RkStore::findOne(['rid' => $wmodel->store_rid]);
        
        $lic = $this->checkLic();       
        $vi = $lic ? 'indexmap' : '/default/_nolic';

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial($vi, [
                        'dataProvider' => $dataProvider,
                        'searchModel' => $searchModel,
                        'wmodel' => $wmodel,
                        'agentName' => $agentModel['denom'],
                        'storeName' => $storeModel['denom'],
                        'isAndroid' => $isAndroid,
                        'vatData' => $vatData
            ]);
        } else {
            return $this->render($vi, [
                        'searchModel' => $searchModel,
                        'dataProvider' => $dataProvider,
                        'wmodel' => $wmodel,
                        'agentName' => $agentModel['denom'],
                        'storeName' => $storeModel['denom'],
                        'isAndroid' => $isAndroid,
                        'vatData' => $vatData
            ]);
        }
    }

    public function actionGetpopover()
    {

        $id = Yii::$app->request->post('key');

        $goodCount = OrderContent::find()->andWhere('order_id = :id',['id' => $id])->count('*');

        $listIds = OrderContent::find()->select('product_id')->andWhere('order_id = :id',['id' => $id])->limit(10)->asArray()->all();

        foreach ($listIds as $ids ) {
            foreach($ids as $key => $value) {
                $fList[]  = $value;
            }
        }

        $listGoods = CatalogBaseGoods::find()->select('product')->andWhere(['IN', 'id', $fList])->asArray()->all();

        $result ="";
        $ind = 1;

        foreach ($listGoods as $ids ) {
            foreach($ids as $key => $value) {
              //  $result  .= $ind++.')&nbsp;'.str_replace(' ', '&nbsp;',$value)."<br>";
                  $result  .= $ind++.')&nbsp;'.$value."<br>";
            }
        }

        if ($goodCount > 10)
            $result  .= "и другие...";
        return $result;

    }

        public function actionChangevat() {
        
      $checked = Yii::$app->request->post('key');
      
      $arr = explode(",", $checked);
      $wbill_id = $arr[1];
      $is_checked = $arr[0]; 
      
      $wmodel = RkWaybill::find()->andWhere('id = :acc', [':acc' => $wbill_id])->one();
      
      if (!$wmodel) {
          die('Waybill model is not found');
      }
      
      if ($is_checked) { // Добавляем НДС
          
      $rress = Yii::$app->db_api
              ->createCommand('UPDATE rk_waybill_data SET sum=round(sum/(vat/10000+1),2) WHERE waybill_id = :acc', [':acc' => $wbill_id])->execute();
      
      $wmodel->vat_included = 1;
      if (!$wmodel->save()) {
          die('Cant save wmodel where vat = 1');
      }
                
      } else { // Убираем НДС
          
      $rress = Yii::$app->db_api
              ->createCommand('UPDATE rk_waybill_data SET sum=defsum WHERE waybill_id = :acc', [':acc' => $wbill_id])->execute();    
      
      $wmodel->vat_included = 0;
      if (!$wmodel->save()) {
          die('Cant save wmodel where vat = 0');
      }
      
      }
       if ($rress) {
           return true;
       } else {
           return false;
       }
       
    }

    public function actionCleardata($id) {

        $model = $this->findDataModel($id);

        $model->quant = $model->defquant;
        $model->koef = 1;
        
        $wmodel = RkWaybill::find()->andWhere('id= :id',[':id' => $model->waybill_id])->one();
        if(!$wmodel) {
            echo "Cant find wmodel in map controller cleardata";
            die();
        }
        
        if ($wmodel->vat_included) {
            $model->sum = round($model->defsum/(1+$model->vat/10000),2);
        } else {
            $model->sum = $model->defsum;
        }
        

        if (!$model->save()) {
            echo $model->getErrors();
            die();
        }

        return $this->redirect(['map', 'waybill_id' => $model->waybill->id]);
    }

    public function actionMakevat($waybill_id, $vat) {

        $model = $this->findModel($waybill_id);

        $rress = Yii::$app->db_api
            ->createCommand('UPDATE rk_waybill_data set vat = :vat, linked_at = now() where waybill_id = :id', [':vat' => $vat, ':id' =>$waybill_id])->execute();
        /*
        $model->quant = $model->defquant;
        $model->koef = 1;

        $wmodel = RkWaybill::find()->andWhere('id= :id',[':id' => $model->waybill_id])->one();
        if(!$wmodel) {
            echo "Cant find wmodel in map controller cleardata";
            die();
        }

        if ($wmodel->vat_included) {
            $model->sum = round($model->defsum/(1+$model->vat/10000),2);
        } else {
            $model->sum = $model->defsum;
        }


        if (!$model->save()) {
            echo $model->getErrors();
            die();
        }
*/
        return $this->redirect(['map', 'waybill_id' => $model->id]);
    }


    public function actionChvat($id, $vat) {

        $model = $this->findDataModel($id);
        $model->vat = $vat;
        $model->save();

        return $this->redirect(['map', 'waybill_id' => $model->waybill->id]);

    }



    public function actionAutocomplete($term = null) {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        // $out = ['results' => ['id' => '0', 'text' => 'Создать контрагента']];
        if (!is_null($term)) {
            $query = new \yii\db\Query;

            // $query->select("`id`, CONCAT(`inn`,`denom`) AS `text`")
            /*
            $query->select(['id' => 'id', 'text' => 'CONCAT(`denom`," (",unitname,")")'])
                    ->from('rk_product')
                    ->where('acc = :acc', [':acc' => User::findOne(Yii::$app->user->id)->organization_id])
                    ->andwhere("denom like :denom ", [':denom' => '%' . $term . '%'])
                    ->limit(20);
            */

            $sql = "( select id, CONCAT(`denom`, '(' ,unitname, ')') as `text` from rk_product where acc = ".User::findOne(Yii::$app->user->id)->organization_id." and denom = '".$term."' )".
            " union ( select id, CONCAT(`denom`, '(' ,unitname, ')') as `text` from rk_product  where acc = ".User::findOne(Yii::$app->user->id)->organization_id." and denom like '".$term."%' limit 15 )".
            "union ( select id, CONCAT(`denom`, '(' ,unitname, ')') as `text` from rk_product where  acc = ".User::findOne(Yii::$app->user->id)->organization_id." and denom like '%".$term."%' limit 10 )".
            "order by case when length(trim(`text`)) = length('".$term."') then 1 else 2 end, `text`; ";

            $db = Yii::$app->db_api;
            $data = $db->createCommand($sql)->queryAll();
            $out['results'] = array_values($data);
        }
        //  $out['results'][] = ['id' => '0', 'text' => 'Создать контрагента'];
        return $out;
    }

    public function actionAutocompleteagent($term = null, $org) {

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (!is_null($term)) {
            $query = new \yii\db\Query;

            // $query->select("`id`, CONCAT(`inn`,`denom`) AS `text`")
            $query->select(['id' => 'rid', 'text' => 'denom'])
                ->from('rk_agent')
                ->where('acc = :acc', [':acc' => $org])
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

            return $this->redirect([$this->getLastUrl().'way='.$model->order_id]);
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

            return $this->redirect([$this->getLastUrl().'way='.$model->order_id]);
        } else {
            return $this->render('create', [
                        'model' => $model,
            ]);
        }
    }
    public function getLastUrl() {

        $lastUrl = Url::previous();
        $lastUrl = substr($lastUrl, strpos($lastUrl,"/clientintegr"));

        $lastUrl = $this->deleteGET($lastUrl,'way');

        if(!strpos($lastUrl,"?")) {
            $lastUrl .= "?";
        } else {
            $lastUrl .= "&";
        }
        return $lastUrl;
    }

    public function deleteGET($url, $name, $amp = true) {
        $url = str_replace("&amp;", "&", $url); // Заменяем сущности на амперсанд, если требуется
        list($url_part, $qs_part) = array_pad(explode("?", $url), 2, ""); // Разбиваем URL на 2 части: до знака ? и после
        parse_str($qs_part, $qs_vars); // Разбиваем строку с запросом на массив с параметрами и их значениями
        unset($qs_vars[$name]); // Удаляем необходимый параметр
        if (count($qs_vars) > 0) { // Если есть параметры
            $url = $url_part."?".http_build_query($qs_vars); // Собираем URL обратно
            if ($amp) $url = str_replace("&", "&amp;", $url); // Заменяем амперсанды обратно на сущности, если требуется
        }
        else $url = $url_part; // Если параметров не осталось, то просто берём всё, что идёт до знака ?
        return $url; // Возвращаем итоговый URL
    }

    public function actionSendws($waybill_id = null) {
        if(is_null($waybill_id)){
            $waybill_id = Yii::$app->request->post('id');
        }
        $model = $this->findModel($waybill_id);

        $res = new \frontend\modules\clientintegr\modules\rkws\components\WaybillHelper();
        $res->sendWaybill($waybill_id);

        return 'true';
       //$this->redirect('/clientintegr/rkws/waybill/index');
    }
    
    /**
     *  Отправка нескольких накладных
     */
    public function actionMultiSend()
    {
        $ids = Yii::$app->request->post('ids');
        $succesCnt = 0;
        foreach ($ids as $id){
            $res = $this->actionSendws($id);
            if ($res === 'true'){
                $succesCnt++;
            }
        }
        return ['success' => true, 'count' => $succesCnt];
    }
    
    /**
     * Отправляем накладную по нажатию кнопки при соспоставлении товаров
     */
    public function actionSendwsByButton()
    {
        /**
        header ("Content-Type:text/xml");
        $id = Yii::$app->request->get('id');
        $model = $this->findModel($id);
        echo $model->getXmlDocument();
        exit;
         */

            $id = Yii::$app->request->post('id');
            $model = $this->findModel($id);
            $error='';


            if (!$model) $error.= 'Не удалось найти накладную. ';

            if ($model->readytoexport==0) $error.= 'Не все товары сопоставлены! ';
            if ($error=='') {
                $res = new \frontend\modules\clientintegr\modules\rkws\components\WaybillHelper();
                $res->sendWaybill($id);
                $model = $this->findModel($id);
                if ($model->status_id!=2) $error.= 'Ошибка при отправке. ';
            }

        if ($error=='') {
                Yii::$app->session->set("rkws_waybill", $model->order_id);
                return 'true';}
                else return $error;
    }

    protected function checkLic() {

        $lic = \api\common\models\RkServicedata::find()->andWhere('org = :org',['org' => Yii::$app->user->identity->organization_id])->one();
    $t = strtotime(date('Y-m-d H:i:s',time()));
    
    if ($lic) {
       /*if ($t >= strtotime($lic->fd) && $t<= strtotime($lic->td) && $lic->status_id === 2 ) {*/
       $res = $lic; 
    /*} else {
       $res = 0; 
    }*/
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

    protected function getEarliestOrder($org_id) {

    $eDate = Order::find()->andWhere(['client_id' => $org_id])->orderBy('updated_at ASC')->one();

    return isset($eDate) ?  $eDate->updated_at : null;

    }


}
