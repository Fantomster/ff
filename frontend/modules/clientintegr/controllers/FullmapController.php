<?php

namespace frontend\modules\clientintegr\controllers;

use api\common\models\AllMaps;
use api\common\models\iiko\iikoProduct;
use api\common\models\iiko\iikoService;
use api\common\models\RkStoretree;
use api\common\models\rkws\OrderCatalogSearchMap;
use api\modules\v1\modules\mobile\resources\OrderCatalogSearch;
use api_web\classes\CartWebApi;
use common\models\AllService;
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

/**
 * Description of FullmapController
 * Controls all the actions of pre-mapping by goods catalog service
 * Author: R.Smirnov
 */
class FullmapController extends DefaultController
{

    public function actionIndex()
    {

        $session = Yii::$app->session;
        $client = $this->currentUser->organization;
        $searchModel = new OrderCatalogSearchMap();
        $params = Yii::$app->request->getQueryParams();

        if (Yii::$app->request->post("OrderCatalogSearchMap")) {
            $params['OrderCatalogSearchMap'] = Yii::$app->request->post("OrderCatalogSearchMap");
            $session['orderCatalogSearchMap'] = Yii::$app->request->post("OrderCatalogSearchMap");
        }

        $params['OrderCatalogSearchMap'] = $session['orderCatalogSearchMap'];

        $currServiceId = isset($params['OrderCatalogSearchMap']) ? $params['OrderCatalogSearchMap']['service_id'] : 0;
        if (isset($currServiceId)) {
            if ($session['service_id'] != $currServiceId)
                $this->actionClearFullmap();
        }

        $session['service_id'] = $currServiceId;

        $selectedCategory = null;
        $selectedVendor = null;

        if (isset($params['OrderCatalogSearchMap'])) {
            $selectedVendor = !empty($params['OrderCatalogSearchMap']['selectedVendor']) ? (int)$params['OrderCatalogSearchMap']['selectedVendor'] : null;
        }
        $vendors = $client->getSuppliers($selectedCategory);
        //$catalogs = $vendors ? $client->getCatalogs($selectedVendor, $selectedCategory) : "(0)";


        $services = ArrayHelper::map(AllService::find()->andWhere('type_id = 1')->all(), 'id', 'denom'); // Add check license
        $services = ['0' => 'Выберите сервис'] + $services;

        $searchModel->client = $client;
        $searchModel->vendors = $vendors;
        //$searchModel->catalogs = $catalogs;

        $dataProvider = $searchModel->search($params);

        $dataProvider->pagination->params['OrderCatalogSearchMap[searchString]'] = isset($params['OrderCatalogSearchMap']['searchString']) ? $params['OrderCatalogSearchMap']['searchString'] : null;
        $dataProvider->pagination->params['OrderCatalogSearchMap[selectedVendor]'] = $selectedVendor;
        $dataProvider->pagination->params['OrderCatalogSearchMap[selectedCategory]'] = $selectedCategory;

        $cart = (new CartWebApi())->items(); //$client->getCart();
        // Вывод по 10
        $dataProvider->pagination->pageSize = 10;

        /*  $lic0 = Organization::getLicenseList();
          //$lic = $this->checkLic();
          $lic = $lic0['rkws'];
          $licucs = $lic0['rkws_ucs'];
          $vi = (($lic) && ($licucs)) ? 'index' : '/default/_nolic';
         */

        $vi = 'index';

        // $page = (array_key_exists('page', $params)) ? $params['page'] : 1;
        // $selected = $session = Yii::$app->session->get('selectedmap', []);
        // $selected = (array_key_exists($page, $selected)) ? $selected[$page] : [];

        $selected = $session->get('selectedmap', []);


        $stores = AllMaps::getStoreListService($searchModel->service_id, $client->id);
        if ($session['service_id'] == 2) {
            $mainOrg = iikoService::getMainOrg($client->id);
        }


        if (Yii::$app->request->isAjax || Yii::$app->request->isPjax) {
            return $this->renderAjax($vi, compact('dataProvider', 'searchModel', 'client', 'cart', 'vendors', 'selectedVendor', 'selected', 'stores', 'services', 'mainOrg'));
        } else {
            return $this->render($vi, compact('dataProvider', 'searchModel', 'client', 'cart', 'vendors', 'selectedVendor', 'selected', 'stores', 'services', 'mainOrg'));
        }
    }

    public function actionEditpdenom($service_id)
    {
        $attr = Yii::$app->request->post('editableAttribute');
        $prod = Yii::$app->request->post('editableKey');
        $rk_product = Yii::$app->request->post('pdenom');

        $res = null;

        $orgs[] = $this->currentUser->organization->id;

        if ($service_id == 2) {
            $orgs = iikoService::getChildOrgsId($this->currentUser->organization->id);
            $orgs[] = $this->currentUser->organization->id;
        }

        $orgs = implode(",", $orgs);

        $hasProducts = AllMaps::find()->andWhere("org_id in ($orgs)")
            ->andWhere('service_id = ' . $service_id . ' and is_active =1')
            ->andWhere('product_id = :prod', [':prod' => $prod])->all();

        foreach ($hasProducts as $hasProduct) {
            $hasProduct->serviceproduct_id = $rk_product;
            $hasProduct->updated_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
            $hasProduct->linked_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');

            if (!$hasProduct->save()) {
                throw new \RuntimeException('Cant update allmaps table.');
            }

            if($hasProduct->org_id == $this->currentUser->organization->id) {
                $res = $hasProduct->getProductNameService();
            }
        }
        if ($res === null) { // New link for mapping creation
            $newProduct = new AllMaps();

            $newProduct->service_id = $service_id;
            $newProduct->org_id = $this->currentUser->organization->id;
            $newProduct->product_id = $prod;
            $newProduct->serviceproduct_id = $rk_product;
            $newProduct->is_active = 1;
            $newProduct->created_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
            $newProduct->updated_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
            $newProduct->linked_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');

            if (!$newProduct->save()) {
                throw new \RuntimeException('Cant save new allmaps model.');
            }

            $res = $newProduct->getProductNameService();
        }

        return Json::encode(['output' => $res, 'message' => '']);
    }

    public function actionEditkoef($service_id)
    {

        $attr = Yii::$app->request->post('editableAttribute');
        $prod = Yii::$app->request->post('editableKey');
        $koef = Yii::$app->request->post('koef');

        $res = null;

        $orgs[] = $this->currentUser->organization->id;

        if ($service_id == 2) {
            $orgs = iikoService::getChildOrgsId($this->currentUser->organization->id);
            $orgs[] = $this->currentUser->organization->id;
        }

        $orgs = implode(",", $orgs);

        $hasProducts = AllMaps::find()->andWhere("org_id in ($orgs)")
            ->andWhere('service_id = ' . $service_id . ' and is_active =1')
            ->andWhere('product_id = :prod', [':prod' => $prod])->all();

        foreach ($hasProducts as $hasProduct) {
            $hasProduct->koef = $koef;
            $hasProduct->updated_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');

            $hasProduct->setScenario('koef');
            if (!$hasProduct->save()) {
                throw new \RuntimeException('Cant update allmaps table.');
            }

            if($hasProduct->org_id == $this->currentUser->organization->id) {
                $res = $hasProduct->koef;
            }
        }
        if ($res === null) { // New link for mapping creation
            $newProduct = new AllMaps();

            $newProduct->service_id = $service_id;
            $newProduct->org_id = $this->currentUser->organization->id;
            $newProduct->product_id = $prod;
            $newProduct->is_active = 1;
            $newProduct->koef = $koef;
            $newProduct->created_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
            $newProduct->updated_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');

            $newProduct->setScenario('koef');
            if (!$newProduct->save()) {
                throw new \RuntimeException('Cant save new allmaps model.');
            }

            $res = $newProduct->koef;
        }
        return Json::encode(['output' => $res, 'message' => '']);
    }

    public function actionEditstore($service_id)
    {

        $attr = Yii::$app->request->post('editableAttribute');
        $prod = Yii::$app->request->post('editableKey');
        $store = Yii::$app->request->post('store');

        $hasProduct = AllMaps::find()->andWhere('org_id = :org', [':org' => $this->currentUser->organization->id,])
            ->andWhere('service_id = ' . $service_id . ' and is_active =1')
            ->andWhere('product_id = :prod', [':prod' => $prod])->one();

        if (!empty($hasProduct)) { // Product link already mapped in table
            $hasProduct->store_rid = $store;
            $hasProduct->updated_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');

            if (!$hasProduct->save()) {
                throw new \RuntimeException('Cant update allmaps table.');
            }

            $res = $hasProduct->store;
        } else { // New link for mapping creation
            $newProduct = new AllMaps();

            if ($service_id == 2) {
                $mainOrg = iikoService::getMainOrg($this->currentUser->organization->id);
                if(isset($mainOrg)) {
                    $hasProduct = AllMaps::find()->andWhere('org_id = :org', [':org' => $mainOrg,])
                        ->andWhere('service_id = ' . $service_id . ' and is_active =1')
                        ->andWhere('product_id = :prod', [':prod' => $prod])->one();
                    if(isset($hasProduct)) {
                        $newProduct->setAttributes($hasProduct->attributes);
                    }
                }
            }

            $newProduct->service_id = $service_id;
            $newProduct->org_id = $this->currentUser->organization->id;
            $newProduct->product_id = $prod;
            $newProduct->is_active = 1;
            $newProduct->store_rid = $store;
            $newProduct->created_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
            $newProduct->updated_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');

            if (!$newProduct->save()) {
                throw new \RuntimeException('Cant save new allmaps model.');
            }

            $res = $newProduct->store;
        }

        switch ($service_id) {
            case 1 :
                $res = $res->name;
                break;
            case 2 :
                $res = $res->denom;
                break;
            case 8 :
                $res = $res->name;
                break;
        }

        return Json::encode(['output' => $res, 'message' => '']);
    }

    public function actionChvat($prod_id, $vat, $service_id)
    {

        $hasProduct = AllMaps::find()->andWhere('org_id = :org', [':org' => $this->currentUser->organization->id,])
            ->andWhere('service_id = ' . $service_id . ' and is_active =1')
            ->andWhere('product_id = :prod', [':prod' => $prod_id])->one();

        if (!empty($hasProduct)) { // Product link already mapped in table
            $hasProduct->vat = $vat;
            $hasProduct->updated_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');

            if (!$hasProduct->save()) {
                throw new \RuntimeException('Cant update allmaps table.');
            }

            $res = $hasProduct->vat;
        } else { // New link for mapping creation
            $newProduct = new AllMaps();

            if ($service_id == 2) {
                $mainOrg = iikoService::getMainOrg($this->currentUser->organization->id);
                if(isset($mainOrg)) {
                    $hasProduct = AllMaps::find()->andWhere('org_id = :org', [':org' => $mainOrg,])
                        ->andWhere('service_id = ' . $service_id . ' and is_active =1')
                        ->andWhere('product_id = :prod', [':prod' => $prod_id])->one();
                    if(isset($hasProduct)) {
                        $newProduct->setAttributes($hasProduct->attributes);
                    }
                }
            }

            $newProduct->service_id = $service_id;
            $newProduct->org_id = $this->currentUser->organization->id;
            $newProduct->product_id = $prod_id;
            $newProduct->is_active = 1;
            $newProduct->vat = $vat;
            $newProduct->created_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
            $newProduct->updated_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');

            if (!$newProduct->save()) {
                throw new \RuntimeException('Cant save new allmaps model.');
            }

            $res = $newProduct->vat;
        }
        return Json::encode(['output' => $res, 'message' => '']);
    }

    public function getLastUrl()
    {

        $lastUrl = Url::previous();
        $lastUrl = substr($lastUrl, strpos($lastUrl, "/clientintegr"));

        $lastUrl = $this->deleteGET($lastUrl, 'way');

        if (!strpos($lastUrl, "?")) {
            $lastUrl .= "?";
        } else {
            $lastUrl .= "&";
        }
        return $lastUrl;
    }

    public function actionAutocomplete($service_id, $term = null)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $sourceTable = '';
        $denomField = '';
        $unitField = '';
        $where = '';
        $orgField = '';

        if (!is_null($term)) {

            switch ($service_id) {
                case 1: // R-keeper
                    $sourceTable = 'rk_product';
                    $denomField = 'denom';
                    $unitField = 'unitname';
                    $orgField = 'acc';
                    break;

                case 2: // iiko
                    $sourceTable = 'iiko_product';
                    $denomField = 'denom';
                    $unitField = 'unit';
                    $orgField = 'org_id';
                    break;

                case 8: // 1C
                    $sourceTable = 'one_s_good';
                    $denomField = 'name';
                    $unitField = 'measure';
                    $orgField = 'org_id';
                    $where = ' and is_category = 0 ';
                    break;
            }

            $sql = "( select id, CONCAT(`" . $denomField . "`, ' (' ," . $unitField . ", ')') as `text` from " . $sourceTable . " where " . $orgField . " = " . User::findOne(Yii::$app->user->id)->organization_id . " and " . $denomField . " = '" . $term . "' " . $where . " )" .
                " union ( select id, CONCAT(`" . $denomField . "`, ' (' ," . $unitField . ", ')') as `text` from " . $sourceTable . "  where " . $orgField . " = " . User::findOne(Yii::$app->user->id)->organization_id . " and  " . $denomField . " like '" . $term . "%'  " . $where . " limit 10 )" .
                "union ( select id, CONCAT(`" . $denomField . "`, ' (' ," . $unitField . ", ')') as `text` from " . $sourceTable . " where  " . $orgField . " = " . User::findOne(Yii::$app->user->id)->organization_id . " and " . $denomField . " like '%" . $term . "%'  " . $where . " limit 5 )" .
                "order by case when length(trim(`text`)) = length('" . $term . "') then 1 else 2 end, `text`; ";

            $db = Yii::$app->db_api;
            $data = $db->createCommand($sql)->queryAll();
            $out['results'] = array_values($data);
        }

        return $out;
    }

    public function actionApplyFullmap()
    {

        $koef = Yii::$app->request->post('koef_set');
        $store = Yii::$app->request->post('store_set');
        $vat = Yii::$app->request->post('vat_set');
        $service_id = Yii::$app->request->post('service_set');

        $koef = 0 + str_replace(',', '.', $koef);

        $valModel = new AllMaps();

        $valModel->org_id = 1;
        $valModel->product_id = 1;
        $valModel->service_id = $service_id;
        $valModel->store_rid = $store;
        $valModel->vat = $vat;
        $valModel->koef = $koef;

        if (!$valModel->validate()) {
            throw new \RuntimeException('Cant validate new allmaps model.');
        }

        $organization = Organization::findOne(User::findOne(Yii::$app->user->id)->organization_id)->id;

        $session = Yii::$app->session;
        $selected = $session->get('selectedmap', []);

        if (empty($selected))
            return true;

        $hasProducts = AllMaps::find()->select('product_id')->andWhere('org_id = :org', [':org' => $this->currentUser->organization->id,])
            ->andWhere('service_id = :s_id and is_active =1', [':s_id' => $service_id])
            ->andWhere(['IN', 'product_id', $selected])->column();

        if (!empty($hasProducts)) {   // Case we have intersection of arrays
            $noProducts = array_diff($selected, $hasProducts);
        } else {
            $noProducts = $selected; // Case all are new
        }

        $selected = implode(',', $selected);
        if ($service_id == 2) {
            $mainOrg = iikoService::getMainOrg($this->currentUser->organization->id);
        }

        foreach ($noProducts as $prod) {

            $model = new AllMaps();
            if ($service_id == 2) {
                if(isset($mainOrg)) {
                    $hasProduct = AllMaps::find()->andWhere('org_id = :org', [':org' => $mainOrg,])
                        ->andWhere('service_id = ' . $service_id . ' and is_active =1')
                        ->andWhere('product_id = :prod', [':prod' => $prod])->one();
                    if(isset($hasProduct)) {
                        $model->setAttributes($hasProduct->attributes);
                    }
                }
            }

            $model->service_id = $service_id;
            $model->org_id = $organization;
            $model->product_id = $prod;
            $model->is_active = 1;
            $model->created_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');

            if (!$model->save()) {
                throw new \RuntimeException('Cant save new allmaps model.');
            }
        }

        if ($koef != -1) {

            $ress = Yii::$app->db_api
                ->createCommand('UPDATE all_map set koef = :koef, updated_at = now() where service_id = ' . $service_id . ' and org_id = :org and product_id in (' . $selected . ')', [':koef' => $koef, ':org' => $organization])->execute();
        }

        if ($store != -1) {
            $ress = Yii::$app->db_api
                ->createCommand('UPDATE all_map set store_rid = :store, updated_at = now() where service_id = ' . $service_id . ' and org_id = :org and product_id in (' . $selected . ')', [':store' => $store, ':org' => $organization])->execute();
        }


        if ($vat != -1) {
            $ress = Yii::$app->db_api
                ->createCommand('UPDATE all_map set vat = :vat, updated_at = now() where service_id = ' . $service_id . ' and org_id = :org and product_id in (' . $selected . ')', [':vat' => $vat, ':org' => $organization])->execute();
        }

        $session->remove('selectedmap');
        return true;
    }

    public function actionClearFullmap()
    {

        $session = Yii::$app->session;

        $session->remove('selectedmap');
        return true;
    }

    public function actionSaveSelectedMaps()
    {
        $selected = Yii::$app->request->get('selected');
        $state = Yii::$app->request->get('state');

        // var_dump ($state);

        $session = Yii::$app->session;

        $list = $session->get('selectedmap', []);

        $current = !empty($selected) ? explode(",", $selected) : [];

        foreach ($current as $item) {

            if ($state) {
                if (!in_array($item, $list))
                    $list[] = $item;
            } else {
                $key = array_search($item, $list);
                unset($list[$key]);
            }
        }

        if (count($list) > 300 && $state) {
            return -1;
        }

        $session->set('selectedmap', $list);
        return true;
    }

    protected function findModel($id)
    {
        if (($model = AllMaps::findOne(['product_id' => $id])) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
