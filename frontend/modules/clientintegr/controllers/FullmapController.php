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
use frontend\modules\clientintegr\modules\iiko\controllers\WaybillController;
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
use api\common\models\iiko\iikoDicconst;
use api\common\models\iiko\iikoPconst;
use api_web\components\Registry;

//use api\common\models\iiko\iikoSelectedProduct;

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
        $orgId = Yii::$app->user->identity->organization_id;

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

        //$services = ['0' => 'Выберите сервис'] + $services;

        $searchModel->client = $client;
        $services = ArrayHelper::map(AllService::find()->andWhere('type_id = 1')->all(), 'id', 'denom'); // Add check license
        $services = ['0' => 'Выберите сервис'] + $services;
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
            ($orgId == $mainOrg) ? $editCan = 1 : $editCan = 0;
        } else {
            $editCan = 1;
        }

        if (Yii::$app->request->isAjax || Yii::$app->request->isPjax) {
            return $this->renderAjax($vi, compact('dataProvider', 'searchModel', 'client', 'cart', 'vendors', 'selectedVendor', 'selected', 'stores', 'services', 'mainOrg', 'editCan'));
        } else {
            return $this->render($vi, compact('dataProvider', 'searchModel', 'client', 'cart', 'vendors', 'selectedVendor', 'selected', 'stores', 'services', 'mainOrg', 'editCan'));
        }
    }

    /*public function actionEditpdenom($service_id)
    {
        $attr = Yii::$app->request->post('editableAttribute');
        $prod = Yii::$app->request->post('editableKey');
        $rk_product = Yii::$app->request->post('pdenom');
        //if ($rk_product==='') return false;
        $res = null;

        $orgs[] = $this->currentUser->organization->id;

        if ($service_id == Registry::IIKO_SERVICE_ID) {
            $orgs = iikoService::getChildOrgsId($this->currentUser->organization->id);
            $orgs[] = $this->currentUser->organization->id;
        }

        if ($service_id == Registry::TILLYPAD_SERVICE_ID) {
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

            if ($hasProduct->org_id == $this->currentUser->organization->id) {
                $res = $hasProduct->getProductNameService();
            }
        }
        if ($res === null) { // New link for mapping creation
            $newProduct = new AllMaps();

            $newProduct->service_id = $service_id;
            $newProduct->org_id = $this->currentUser->organization->id;
            $newProduct->product_id = $prod;
            $newProduct->supp_id = CatalogBaseGoods::getSuppById($newProduct->product_id);
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
        //if ($rk_product==='') $res->prod='пусто';
        //if ($rk_product===null) $res->prod='нуль';
        return Json::encode(['output' => $res, 'message' => '']);
    }*/

    public function actionEditkoef($service_id)
    {

        $attr = Yii::$app->request->post('editableAttribute');
        $prod = Yii::$app->request->post('editableKey');
        $koef = Yii::$app->request->post('koef');

        $res = null;

        $orgs[] = $this->currentUser->organization->id;

        if ($service_id == Registry::IIKO_SERVICE_ID) {
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

            if ($hasProduct->org_id == $this->currentUser->organization->id) {
                $res = $hasProduct->koef;
            }
        }
        if ($res === null) { // New link for mapping creation
            $newProduct = new AllMaps();

            $newProduct->service_id = $service_id;
            $newProduct->org_id = $this->currentUser->organization->id;
            $newProduct->product_id = $prod;
            $newProduct->supp_id = CatalogBaseGoods::getSuppById($newProduct->product_id);
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

        $prod_id = Yii::$app->request->post('editableKey');
        $store = Yii::$app->request->post('store');
        $org_id = $this->currentUser->organization->id;

        $product = AllMaps::find()->where('org_id = :org and service_id = :serv and is_active = :active and product_id = :prod',
                                                    [':org' => $org_id, ':serv' => $service_id, ':active' => 1, ':prod' => $prod_id])->one();

        if (!empty($product)) { // Product link already mapped in table
            $product->store_rid = $store;

            if (!$product->save()) {
                throw new \RuntimeException('Cant update allmaps table.');
            }
        } else { // New link for mapping creation
            $product = new AllMaps();

            $product->service_id = $service_id;
            $product->org_id = $org_id;
            $product->product_id = $prod_id;
            $product->supp_id = CatalogBaseGoods::getSuppById($product->product_id);
            $product->is_active = 1;
            $product->store_rid = $store;

            if (!$product->save()) {
                throw new \RuntimeException('Cant save new allmaps model.');
            }
        }
        $res = $product->store;

        switch ($service_id) {
            case Registry::RK_SERVICE_ID :
                $res_name = $res->name;
                break;
            case Registry::IIKO_SERVICE_ID :
                $res_name = $res->denom;
                break;
            case Registry::ONE_S_CLIENT_SERVICE_ID :
                $res_name = $res->name;
                break;
            case Registry::TILLYPAD_SERVICE_ID :
                $res_name = $res->denom;
                break;
        }

        return Json::encode(['output' => $res_name, 'message' => '']);
    }

    public function actionChvat() // устанавливает ставку НДС для одной позиции в глобальном сопоставлении
    {

        $prod_id = Yii::$app->request->post('prod_id');
        $vat = Yii::$app->request->post('vat');
        $service_id = Yii::$app->request->post('service_id');
        $org_id = $this->currentUser->organization->id;
        $product = AllMaps::find()->andWhere('org_id = :org', [':org' => $org_id])
            ->andWhere('service_id = :serv', [':serv' => $service_id])
            ->andWhere('is_active = :active', [':active' => 1])
            ->andWhere('product_id = :prod', [':prod' => $prod_id])->one();

        if (!empty($Product)) { // Product link already mapped in table
            $Product->vat = $vat;

            if (!$Product->save()) {
                throw new \RuntimeException('Cant update allmaps table.');
            }
        } else { // New link for mapping creation
            $Product = new AllMaps();
            $Product->service_id = $service_id;
            $Product->org_id = $this->currentUser->organization->id;
            $Product->product_id = $prod_id;
            $Product->supp_id = CatalogBaseGoods::getSuppById($prod_id);
            $Product->is_active = 1;
            $Product->vat = $vat;

            if (!$Product->save()) {
                throw new \RuntimeException('Cant save new allmaps model.');
            }
        }

        if ($service_id == Registry::IIKO_SERVICE_ID) {
            $childs = iikoService::getChildOrgsId($org_id);
            if (!empty($childs)) {
                foreach ($childs as $child) {
                    $child_product = AllMaps::find()->andWhere('org_id = :org', [':org' => $child,])
                        ->andWhere('service_id = :serv', [':serv' => $service_id])
                        ->andWhere('is_active = :active', [':active' => 1])
                        ->andWhere('product_id = :prod', [':prod' => $prod_id])->one();
                    if (!empty($child_product)) {
                        if ($child_product->vat === null) {
                            $child_product->vat = $vat;
                        }
                        if (!$child_product->save()) {
                            throw new \RuntimeException('Cant update allmaps table.');
                        }
                    } else {
                        $child_product = new AllMaps();
                        $child_product->vat = $vat;
                        $child_product->service_id = $service_id;
                        $child_product->org_id = $child;
                        $child_product->product_id = $prod_id;
                        $child_product->supp_id = CatalogBaseGoods::getSuppById($prod_id);
                        $child_product->serviceproduct_id = $Product->serviceproduct_id;
                        $child_product->unit_rid = null;
                        $child_product->store_rid = null;
                        $child_product->koef = $Product->koef ?? 1;
                        $child_product->is_active = 1;
                        if (!$child_product->save()) {
                            throw new \RuntimeException('Cant save new allmaps model.');
                        }
                    }
                }
            }
        }

        $res = $Product->vat;
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
                case Registry::RK_SERVICE_ID: // R-keeper
                    $sourceTable = 'rk_product';
                    $denomField = 'denom';
                    $unitField = 'unitname';
                    $orgField = 'acc';
                    break;

                case Registry::IIKO_SERVICE_ID: // iiko
                    $sourceTable = 'iiko_product';
                    $denomField = 'denom';
                    $unitField = 'unit';
                    $orgField = 'org_id';
                    break;

                case Registry::ONE_S_CLIENT_SERVICE_ID: // 1C
                    $sourceTable = 'one_s_good';
                    $denomField = 'name';
                    $unitField = 'measure';
                    $orgField = 'org_id';
                    $where = ' and is_category = 0 ';
                    break;

                case Registry::TILLYPAD_SERVICE_ID: // tillypad
                    $sourceTable = 'iiko_product';
                    $denomField = 'denom';
                    $unitField = 'unit';
                    $orgField = 'org_id';
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
        $valModel->supp_id = CatalogBaseGoods::getSuppById($valModel->product_id);
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
        if ($service_id == Registry::IIKO_SERVICE_ID) {
            $mainOrg = iikoService::getMainOrg($this->currentUser->organization->id);
        }

        if ($service_id == Registry::TILLYPAD_SERVICE_ID) {
            $mainOrg = iikoService::getMainOrg($this->currentUser->organization->id);
        }

        foreach ($noProducts as $prod) {

            $model = new AllMaps();
            if ($service_id == Registry::IIKO_SERVICE_ID) {
                if (isset($mainOrg)) {
                    $hasProduct = AllMaps::find()->andWhere('org_id = :org', [':org' => $mainOrg,])
                        ->andWhere('service_id = ' . $service_id . ' and is_active =1')
                        ->andWhere('product_id = :prod', [':prod' => $prod])->one();
                    if (isset($hasProduct)) {
                        $model->setAttributes($hasProduct->attributes);
                    }
                }
            }

            if ($service_id == Registry::TILLYPAD_SERVICE_ID) {
                if (isset($mainOrg)) {
                    $hasProduct = AllMaps::find()->andWhere('org_id = :org', [':org' => $mainOrg,])
                        ->andWhere('service_id = ' . $service_id . ' and is_active =1')
                        ->andWhere('product_id = :prod', [':prod' => $prod])->one();
                    if (isset($hasProduct)) {
                        $model->setAttributes($hasProduct->attributes);
                    }
                }
            }

            $model->service_id = $service_id;
            $model->org_id = $organization;
            $model->product_id = $prod;
            $model->supp_id = CatalogBaseGoods::getSuppById($model->product_id);
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

    public function actionAutoCompleteSelectedProducts()
    {
        $orgId = User::findOne(Yii::$app->user->id)->organization_id;
        $constId = iikoDicconst::findOne(['denom' => 'main_org']);
        $parentId = iikoPconst::findOne(['const_id' => $constId->id, 'org' => $orgId]);

        $organizationID = (isset($parentId, $parentId->value) && strlen((int)$parentId->value) ==
            strlen($parentId->value) && $parentId->value > 0) ? $parentId->value : $orgId;

        $sql = "SELECT COUNT(*) FROM iiko_selected_product WHERE organization_id = :w_org";
        $result = Yii::$app->db_api->createCommand($sql, [':w_org' => $organizationID])->queryScalar();

        return $result;
    }

    public function actionAutoCompleteNew()
    {
        $term = Yii::$app->request->post('stroka');
        $us = Yii::$app->request->post('us');
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if ($term == '') {
            $term = null;
        }
        $out = [];
        switch ($us) {
            case Registry::RK_SERVICE_ID:
                $out = \frontend\modules\clientintegr\modules\rkws\controllers\WaybillController::actionAutoCompleteNew($term);
                break;
            case Registry::IIKO_SERVICE_ID:
                $out = \frontend\modules\clientintegr\modules\iiko\controllers\WaybillController::actionAutoCompleteNew($term);
                break;
            case Registry::ONE_S_CLIENT_SERVICE_ID:
                $out = \frontend\modules\clientintegr\modules\odinsobsh\controllers\WaybillController::actionAutoCompleteNew($term);
                break;
            case Registry::TILLYPAD_SERVICE_ID:
                $out = \frontend\modules\clientintegr\modules\tillypad\controllers\WaybillController::actionAutoCompleteNew($term);
                break;
        }
        return $out;
    }

    public function actionEditNew()
    {
        $us = Yii::$app->request->post('us');
        switch ($us) {
            case '1':
                $munit = \frontend\modules\clientintegr\modules\rkws\controllers\WaybillController::actionEditGlobal();
                break;
            case '2':
                $munit = \frontend\modules\clientintegr\modules\iiko\controllers\WaybillController::actionEditGlobal();
                break;
            case '8':
                $munit = \frontend\modules\clientintegr\modules\odinsobsh\controllers\WaybillController::actionEditGlobal();
                break;
            case '10':
                $munit = \frontend\modules\clientintegr\modules\tillypad\controllers\WaybillController::actionEditGlobal();
                break;
        }
        return $munit;
    }

    /** Проверяет и добавляет в all_map все записи главного бизнеса для дочерних бизнесов
     */
    public function actionAddAllChildsProductsFromMain($parent_id)
    {
        $obConstModel = iikoDicconst::findOne(['denom' => 'main_org']); // Получаем идентификатор константы бизнеса для сопоставления
        $arChildsModels = iikoPconst::find()->select('org')->where(['const_id' => $obConstModel->id, 'value' => $parent_id])->all(); //получаем дочерние бизнесы
        $allMainProducts = AllMaps::find()->select('service_id, product_id, supp_id, serviceproduct_id, koef, vat, is_active')->where(['org_id' => $parent_id, 'service_id' => Registry::IIKO_SERVICE_ID])->all();
        foreach ($arChildsModels as $child) {
            foreach ($allMainProducts as $main_product) {
                $child_product = AllMaps::find()->select('id, store_rid, vat')->where(['org_id' => $child->org, 'service_id' => Registry::IIKO_SERVICE_ID, 'product_id' => $main_product->product_id])->one();
                if ($child_product) {
                    $ChildProduct = AllMaps::findOne($child_product->id);
                    (is_null($child_product->store_rid)) ? $ChildProduct->store_rid = null : $ChildProduct->store_rid = $child_product->store_rid;
                    (is_null($child_product->vat)) ? $ChildProduct->vat = null : $ChildProduct->vat = $child_product->vat;
                } else {
                    $ChildProduct = new AllMaps();
                    $ChildProduct->store_rid = null;
                    $ChildProduct->vat = $main_product->vat;
                }
                $ChildProduct->service_id = $main_product->service_id;
                $ChildProduct->koef = $main_product->koef;
                $ChildProduct->org_id = $child->org;
                $ChildProduct->product_id = $main_product->product_id;
                $ChildProduct->supp_id = $main_product->supp_id;
                $ChildProduct->serviceproduct_id = $main_product->serviceproduct_id;
                $ChildProduct->is_active = $main_product->is_active;
                if (!is_null($ChildProduct->serviceproduct_id)) {
                    $ChildProduct->linked_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
                }
                try {
                    if (!$ChildProduct->save()) {
                        throw new \Exception('Не удалось сохранить продукт дочернего бизнеса.');
                    }
                } catch (\Exception $e) {
                    \yii::error('Не удалось сохранить продукт ' . $main_product->id . ' дочернего бизнеса ' . $child->org);
                    return false;
                }
            }
        }
        return true;
    }

    /** Проверяет и добавляет в all_map сопоставленную запись из главного бизнеса в дочерние бизнесы
     */
    public function actionAddProductFromMain($parent_id, $product_id)
    {
        $obConstModel = iikoDicconst::findOne(['denom' => 'main_org']); // Получаем идентификатор константы бизнеса для сопоставления
        $arChildsModels = iikoPconst::find()->select('org')->where(['const_id' => $obConstModel->id, 'value' => $parent_id])->all(); //получаем дочерние бизнесы
        $main_product = AllMaps::find()->select('service_id, product_id, supp_id, serviceproduct_id, koef, vat, is_active')->where(['org_id' => $parent_id, 'service_id' => 2, 'product_id' => $product_id])->one();
        foreach ($arChildsModels as $child) {
            $child_product = AllMaps::find()->select('id, store_rid, vat')->where(['org_id' => $child->org, 'service_id' => Registry::IIKO_SERVICE_ID, 'product_id' => $main_product->product_id])->one();
            if ($child_product) {
                $ChildProduct = AllMaps::findOne($child_product->id);
                (is_null($child_product->store_rid)) ? $ChildProduct->store_rid = null : $ChildProduct->store_rid = $child_product->store_rid;
                (is_null($child_product->vat)) ? $ChildProduct->vat = null : $ChildProduct->vat = $child_product->vat;
            } else {
                $ChildProduct = new AllMaps();
                $ChildProduct->store_rid = null;
                $ChildProduct->vat = $main_product->vat;
            }
            $ChildProduct->service_id = $main_product->service_id;
            $ChildProduct->koef = $main_product->koef;
            $ChildProduct->org_id = $child->org;
            $ChildProduct->product_id = $main_product->product_id;
            $ChildProduct->supp_id = $main_product->supp_id;
            $ChildProduct->serviceproduct_id = $main_product->serviceproduct_id;
            $ChildProduct->is_active = $main_product->is_active;
            if (!is_null($ChildProduct->serviceproduct_id)) {
                $ChildProduct->linked_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
            }
            try {
                if (!$ChildProduct->save()) {
                    throw new \Exception('Не удалось сохранить продукт дочернего бизнеса.');
                }
            } catch (\Exception $e) {
                \yii::error('Не удалось сохранить продукт ' . $main_product->id . ' дочернего бизнеса ' . $child->org);
                return false;
            }
        }
        return true;
    }

}
