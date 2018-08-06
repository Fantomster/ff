<?php

namespace frontend\modules\clientintegr\modules\rkws\controllers;

use api\common\models\AllMaps;
use api\common\models\RkStoretree;
use api\common\models\rkws\OrderCatalogSearchMap;
use api\modules\v1\modules\mobile\resources\OrderCatalogSearch;
use api_web\classes\CartWebApi;
use common\models\CatalogBaseGoods;
use common\models\OrderContent;
use Yii;
use yii\helpers\Json;
use yii\web\Controller;
use api\common\models\RkWaybill;
use api\common\models\RkAgentSearch;
use frontend\modules\clientintegr\modules\rkws\components\ApiHelper;
use api\common\models\RkWaybilldata;
use yii\data\ActiveDataProvider;
use common\models\User;
use yii\helpers\ArrayHelper;
use kartik\grid\EditableColumnAction;
use common\models\Organization;
use yii\helpers\Url;
use frontend\modules\clientintegr\modules\rkws\components\FullmapHelper;



// use yii\mongosoft\soapserver\Action;

/**
 * Description of FullmapController
 * Controls all the actions of pre-mapping by goods catalog service
 * Author: R.Smirnov
 */

class FullmapController extends \frontend\modules\clientintegr\controllers\DefaultController {

    public function actions() {
        return ArrayHelper::merge(parent::actions(), [

            'changekoef' => [// identifier for your editable column action
                'class' => EditableColumnAction::className(), // action class name
                'modelClass' => AllMaps::className(), // the model for the record being edited
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

            ]
        ]);
    }

    public function actionIndex() {

        $session = Yii::$app->session;
        $client = $this->currentUser->organization;
        $searchModel = new OrderCatalogSearchMap();
        $params = Yii::$app->request->getQueryParams();

        if (Yii::$app->request->post("OrderCatalogSearchMap")) {
            $params['OrderCatalogSearchMap'] = Yii::$app->request->post("OrderCatalogSearchMap");
            $session['orderCatalogSearchMap'] = Yii::$app->request->post("OrderCatalogSearchMap");
        }

        $selectedCategory = null;
        $selectedVendor = null;

        if (isset($params['OrderCatalogSearchMap'])) {
            $selectedVendor = !empty($params['OrderCatalogSearchMap']['selectedVendor']) ? (int) $params['OrderCatalogSearchMap']['selectedVendor'] : null;
        }
        $vendors = $client->getSuppliers($selectedCategory);
        $catalogs = $vendors ? $client->getCatalogs($selectedVendor, $selectedCategory) : "(0)";

        $searchModel->client = $client;
        $searchModel->catalogs = $catalogs;

        $params['OrderCatalogSearchMap'] = $session['orderCatalogSearchMap'];
        $dataProvider = $searchModel->search($params);

        $dataProvider->pagination->params['OrderCatalogSearchMap[searchString]'] = isset($params['OrderCatalogSearchMap']['searchString']) ? $params['OrderCatalogSearchMap']['searchString'] : null;
        $dataProvider->pagination->params['OrderCatalogSearchMap[selectedVendor]'] = $selectedVendor;
        $dataProvider->pagination->params['OrderCatalogSearchMap[selectedCategory]'] = $selectedCategory;

        $cart = (new CartWebApi())->items(); //$client->getCart();
        // Вывод по 10
        $dataProvider->pagination->pageSize = 10;

        $lic0 = Organization::getLicenseList();
        //$lic = $this->checkLic();
        $lic = $lic0['rkws'];
        $licucs = $lic0['rkws_ucs'];
        $vi = (($lic) && ($licucs)) ? 'index' : '/default/_nolic';

        // $page = (array_key_exists('page', $params)) ? $params['page'] : 1;
        // $selected = $session = Yii::$app->session->get('selectedmap', []);
        // $selected = (array_key_exists($page, $selected)) ? $selected[$page] : [];

        $selected = $session->get('selectedmap', []);

        $stores = [-1 => 'Не менять'];
        $stores +=  ArrayHelper::map(RkStoretree::find()->andWhere('acc=:acc',[':acc' => $client->id])->all(), 'rid', 'name');


        if (Yii::$app->request->isAjax || Yii::$app->request->isPjax ) {
            return $this->renderAjax($vi, compact('dataProvider', 'searchModel', 'client',
                'cart', 'vendors', 'selectedVendor', 'lic', 'licucs', 'selected', 'stores'));
        } else {
                return $this->render($vi, compact('dataProvider', 'searchModel', 'client',
                    'cart', 'vendors', 'selectedVendor', 'lic', 'licucs','selected', 'stores'));
        }

/*
        $organization = Organization::findOne(User::findOne(Yii::$app->user->id)->organization_id)->id;

        $records =  AllMaps::find()->select('*')->andWhere('org_id = :org',["org" => $organization])
            ->andWhere('service_id = 1');

        $dataProvider = new ActiveDataProvider(['query' => $records,
            'sort' => ['defaultOrder' => ['id' => SORT_ASC]],
        ]);

        $lic0 = Organization::getLicenseList();
        //$lic = $this->checkLic();
        $lic = $lic0['rkws'];
        $licucs = $lic0['rkws_ucs'];
        $vi = (($lic) && ($licucs)) ? 'index' : '/default/_nolic';

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial($vi,[
                'dataProvider' => $dataProvider,
                'lic' => $lic,
                'licucs' => $licucs,
            ]);
        } else {
            return $this->render($vi,[
                'dataProvider' => $dataProvider,
                'lic' => $lic,
                'licucs' => $licucs,
            ]);
        }
*/
    }

    public function actionEditpdenom() {
        $attr = Yii::$app->request->post('editableAttribute');
        $key = Yii::$app->request->post('editableKey');
        $pdenom = Yii::$app->request->post('pdenom');

      //  var_dump($attr);
      //  var_dump($key);
      //  var_dump($pdenom);

        return Json::encode(['output' => 'Тмин', 'message' => '']);


    }

    public function actionRenewcats() {
        return true; //for great justice!
        $helper = new FullmapHelper();

        $helper->getcats();

       // return $this->redirect(['index']);
          return true;
    }

    public function actionChvat($id, $vat) {

        $model = $this->findModel($id);

        $rress = Yii::$app->db_api
            ->createCommand('UPDATE all_map set vat = :vat, linked_at = now() where id = :id', [':vat' => $vat, ':id' =>$id])->execute();

        return $this->redirect(['index']);

    }

    public function actionMakevat($vat) {

        $organization = Organization::findOne(User::findOne(Yii::$app->user->id)->organization_id)->id;

        $rress = Yii::$app->db_api
            ->createCommand('UPDATE all_map set vat = :vat, linked_at = now() where service_id = 1 and org_id = :org',
                [':vat' => $vat, ':org' => $organization])->execute();

        return $this->redirect(['index']);
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

    public function actionAutocomplete($term = null) {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (!is_null($term)) {

            $sql = "( select id, CONCAT(`denom`, ' (' ,unitname, ')') as `text` from rk_product where acc = ".User::findOne(Yii::$app->user->id)->organization_id." and denom = '".$term."' )".
                " union ( select id, CONCAT(`denom`, ' (' ,unitname, ')') as `text` from rk_product  where acc = ".User::findOne(Yii::$app->user->id)->organization_id." and denom like '".$term."%' limit 10 )".
                "union ( select id, CONCAT(`denom`, ' (' ,unitname, ')') as `text` from rk_product where  acc = ".User::findOne(Yii::$app->user->id)->organization_id." and denom like '%".$term."%' limit 5 )".
                "order by case when length(trim(`text`)) = length('".$term."') then 1 else 2 end, `text`; ";

            $db = Yii::$app->db_api;
            $data = $db->createCommand($sql)->queryAll();
            $out['results'] = array_values($data);
        }

        return $out;
    }

    protected function checkLic() {

        $lic = \api\common\models\RkServicedata::find()->andWhere('org = :org',['org' => Yii::$app->user->identity->organization_id])->one();
        $t = strtotime(date('Y-m-d H:i:s',time()));

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


    public function actionApplyFullmap() {

        $session = Yii::$app->session;

        $selected = $session->get('selectedmap', []);
        if (empty($selected))
            return true;

        $selected = implode(',', $selected);

        // Update where eta hernya

        $session->remove('selectedmap');
        return true;

    }

    public function actionClearFullmap() {

       $session = Yii::$app->session;

        $session->remove('selectedmap');
        return true;

    }

    public function actionSaveSelectedMaps() {
        $selected = Yii::$app->request->get('selected');
        $state = Yii::$app->request->get('state');

        var_dump ($state);

        $session = Yii::$app->session;

        $list = $session->get('selectedmap', []);

        $current = !empty($selected) ? explode(",", $selected) : [];

        foreach ($current as $item) {

            if ($state)
            {
               if (!in_array($item,$list))
                $list[] = $item;
            } else {
                $key = array_search ($item, $list);
                unset($list[$key]);
            }

        }

        $session->set('selectedmap', $list);
        return true;
    }

    protected function findModel($id) {
        if (($model = AllMaps::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
