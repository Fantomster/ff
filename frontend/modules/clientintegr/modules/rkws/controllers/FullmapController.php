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
use yii\helpers\VarDumper;
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

        $stores = [-1 => 'Нет'];
        $stores +=  ArrayHelper::map(RkStoretree::find()->andWhere('acc=:acc',[':acc' => $client->id])->
        andWhere('type = 2')->all(), 'rid', 'name');


        if (Yii::$app->request->isAjax || Yii::$app->request->isPjax ) {
            return $this->renderAjax($vi, compact('dataProvider', 'searchModel', 'client',
                'cart', 'vendors', 'selectedVendor', 'lic', 'licucs', 'selected', 'stores'));
        } else {
                return $this->render($vi, compact('dataProvider', 'searchModel', 'client',
                    'cart', 'vendors', 'selectedVendor', 'lic', 'licucs','selected', 'stores'));
        }

    }

    public function actionEditpdenom() {
        $attr = Yii::$app->request->post('editableAttribute');
        $prod = Yii::$app->request->post('editableKey');
        $rk_product = Yii::$app->request->post('pdenom');

      //  var_dump($attr);
      //  var_dump($key);
      //  var_dump($pdenom);

       $hasProduct = AllMaps::find()->andWhere('org_id = :org',[':org' => $this->currentUser->organization->id,])
           ->andWhere('service_id = 1 and is_active =1')
           ->andWhere('product_id = :prod',[':prod' => $prod])->one();

       if (!empty($hasProduct)) { // Product link already mapped in table

           $hasProduct->serviceproduct_id = $rk_product;
           $hasProduct->updated_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
           $hasProduct->linked_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');

           if (!$hasProduct->save(false)){
               throw new \RuntimeException('Cant update allmaps table.');
           }

           $res = $hasProduct->productrk->denom;

       } else { // New link for mapping creation

           $newProduct = new AllMaps();

           $newProduct->service_id =1;
           $newProduct->org_id =  $this->currentUser->organization->id;
           $newProduct->product_id = $prod;
           $newProduct->serviceproduct_id = $rk_product;
           $newProduct->is_active = 1;
           $newProduct->created_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
           $newProduct->updated_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
           $newProduct->linked_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');

           if (!$newProduct->save(false)){
               throw new \RuntimeException('Cant save new allmaps model.');
           }

           $res = $newProduct->productrk->denom;

       }

        return Json::encode(['output' => $res, 'message' => '']);

    }


    public function actionEditkoef() {

        $attr = Yii::$app->request->post('editableAttribute');
        $prod = Yii::$app->request->post('editableKey');
        $koef = Yii::$app->request->post('koef');

        $hasProduct = AllMaps::find()->andWhere('org_id = :org',[':org' => $this->currentUser->organization->id,])
            ->andWhere('service_id = 1 and is_active =1')
            ->andWhere('product_id = :prod',[':prod' => $prod])->one();

        if (!empty($hasProduct)) { // Product link already mapped in table

            $hasProduct->koef = $koef;
            $hasProduct->updated_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');

            if (!$hasProduct->save()){
                throw new \RuntimeException('Cant update allmaps table.');
            }

            $res = $hasProduct->koef;

        } else { // New link for mapping creation

            $newProduct = new AllMaps();

            $newProduct->service_id =1;
            $newProduct->org_id =  $this->currentUser->organization->id;
            $newProduct->product_id = $prod;
            $newProduct->is_active = 1;
            $newProduct->koef = $koef;
            $newProduct->created_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
            $newProduct->updated_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');

            if (!$newProduct->save()){
                throw new \RuntimeException('Cant save new allmaps model.');
            }

            $res = $newProduct->koef;
        }
        return Json::encode(['output' => $res, 'message' => '']);

    }

    public function actionEditstore() {

        $attr = Yii::$app->request->post('editableAttribute');
        $prod = Yii::$app->request->post('editableKey');
        $store = Yii::$app->request->post('store');

        $hasProduct = AllMaps::find()->andWhere('org_id = :org',[':org' => $this->currentUser->organization->id,])
            ->andWhere('service_id = 1 and is_active =1')
            ->andWhere('product_id = :prod',[':prod' => $prod])->one();

        if (!empty($hasProduct)) { // Product link already mapped in table

            $hasProduct->store_rid = $store;
            $hasProduct->updated_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');

            if (!$hasProduct->save(false)){
                throw new \RuntimeException('Cant update allmaps table.');
            }

            $res = $hasProduct->store->name;

        } else { // New link for mapping creation

            $newProduct = new AllMaps();

            $newProduct->service_id =1;
            $newProduct->org_id =  $this->currentUser->organization->id;
            $newProduct->product_id = $prod;
            $newProduct->is_active = 1;
            $newProduct->store_rid = $store;
            $newProduct->created_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
            $newProduct->updated_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');

            if (!$newProduct->save(false)){
                throw new \RuntimeException('Cant save new allmaps model.');
            }

            $res = $newProduct->store->name;

        }


        return Json::encode(['output' => $res, 'message' => '']);

    }

    public function actionRenewcats() {
        return true; //for great justice!
        $helper = new FullmapHelper();

        $helper->getcats();

       // return $this->redirect(['index']);
          return true;
    }

    public function actionChvat($id, $vat) {

        // $model = $this->findModel($id);

        $rress = Yii::$app->db_api
            ->createCommand('UPDATE all_map set vat = :vat, linked_at = now() where product_id = :id', [':vat' => $vat, ':id' =>$id])->execute();

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

            $sql = "( select id, CONCAT(denom, ' (' ,unitname, ')') as txt from rk_product where acc = " . User::findOne(Yii::$app->user->id)->organization_id . " and denom = '" . $term . "' )" .
                " union ( select id, CONCAT(denom, ' (' ,unitname, ')') as txt from rk_product  where acc = " . User::findOne(Yii::$app->user->id)->organization_id . " and denom like '" . $term . "%' limit 10 )" .
                "union ( select id, CONCAT(denom, ' (' ,unitname, ')') as txt from rk_product where  acc = " . User::findOne(Yii::$app->user->id)->organization_id . " and denom like '%" . $term . "%' limit 5 )" .
                "order by case when length(trim(txt)) = length('" . $term . "') then 1 else 2 end, txt; ";

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

        $koef = Yii::$app->request->post('koef_set');
        $store = Yii::$app->request->post('store_set');
        $vat = Yii::$app->request->post('vat_set');

        $organization = Organization::findOne(User::findOne(Yii::$app->user->id)->organization_id)->id;

        $session = Yii::$app->session;
        $hasProductsFinal = [];

        $selected = $session->get('selectedmap', []);
        if (empty($selected))
            return true;

        $hasProducts = AllMaps::find()->select('product_id')->andWhere('org_id = :org',[':org' => $this->currentUser->organization->id,])
            ->andWhere('service_id = 1 and is_active =1')
            ->andWhere(['IN','product_id',$selected])->asArray()->all();

        // Find Ids which are not in the all_map table but should be created as presents in $selected

        if (!empty($hasProducts)) {   // Case we have intersection of arrays
            foreach ($hasProducts as $p => $k) {
                foreach($k as $t => $v) {
                    array_push($hasProductsFinal,$v);
                }
            }
            $noProducts = array_diff($selected,$hasProductsFinal);
        } else {
            $noProducts = $selected; // Case all are new
        }

        $selected = implode(',', $selected);

        foreach ($noProducts as $prod) {

            $model = new AllMaps();

            $model->service_id =1;
            $model->org_id =  $organization;
            $model->product_id = $prod;
            $model->is_active = 1;
            $model->created_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');

            if (!$model->save(false)){
                throw new \RuntimeException('Cant save new allmaps model.');
            }

        }

        if($koef != -1) {

            $ress = Yii::$app->db_api
                ->createCommand('UPDATE all_map set koef = :koef, updated_at = now() where service_id = 1 and org_id = :org and product_id in ('.$selected.')',
                    [':koef' => $koef, ':org' => $organization])->execute();

        }

        if($store != -1) {
            $ress = Yii::$app->db_api
                ->createCommand('UPDATE all_map set store_rid = :store, updated_at = now() where service_id = 1 and org_id = :org and product_id in ('.$selected.')',
                    [':store' => $store, ':org' => $organization])->execute();
        }


        if($vat != -1) {
            $ress = Yii::$app->db_api
                ->createCommand('UPDATE all_map set vat = :vat, updated_at = now() where service_id = 1 and org_id = :org and product_id in ('.$selected.')',
                    [':vat' => $vat, ':org' => $organization])->execute();
        }

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
        if (($model = AllMaps::findOne(['product_id' => $id])) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
