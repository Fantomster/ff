<?php

namespace frontend\modules\clientintegr\controllers;

use api\common\models\AllMaps;
use api\common\models\iiko\iikoService;
use api\common\models\one_s\OneSStore;
use api\common\models\RkStoretree;
use api\common\models\rkws\OrderCatalogSearchMap;
use api_web\modules\integration\classes\sync\IikoStore;
use common\models\CatalogBaseGoods;
use Yii;
use yii\helpers\Json;
use common\models\User;
use common\models\Organization;
use yii\helpers\Url;
use api\common\models\iiko\iikoDicconst;
use api\common\models\iiko\iikoPconst;
use api_web\components\Registry;
use common\models\RelationSuppRest;
use api\common\models\iiko\iikoSelectedProduct;
use yii\web\NotFoundHttpException;

/**
 * Description of FullmapController
 * Controls all the actions of pre-mapping by goods catalog service
 */
class FullmapController extends DefaultController
{

    public function actionIndex() // метод загрузки данных и открытия страницы Массового сопоставления
    {
        $client = $this->currentUser->organization;
        $searchModel = new OrderCatalogSearchMap();
        $params = Yii::$app->request->getQueryParams();
        $orgId = Yii::$app->user->identity->organization_id;

        if (Yii::$app->request->post("OrderCatalogSearchMap")) {
            $params['OrderCatalogSearchMap'] = Yii::$app->request->post("OrderCatalogSearchMap");
            $service = $params['OrderCatalogSearchMap']['service_id'];
        } elseif (isset($params['OrderCatalogSearchMap']['service_id'])) {
            $service = $params['OrderCatalogSearchMap']['service_id'];
        } else {
            $service = 0;
        }

        $selectedCategory = null;
        $selectedVendor = null;

        if (isset($params['OrderCatalogSearchMap'])) {
            $selectedVendor = !empty($params['OrderCatalogSearchMap']['selectedVendor']) ? (int)$params['OrderCatalogSearchMap']['selectedVendor'] : null;
        }
        $vendors = $client->getSuppliers($selectedCategory);

        $searchModel->client = $client;
        $searchModel->vendors = $vendors;
        $searchModel->service_id = $service;

        $dataProvider = $searchModel->search($params);

        $dataProvider->pagination->params['OrderCatalogSearchMap[searchString]'] = isset($params['OrderCatalogSearchMap']['searchString']) ? $params['OrderCatalogSearchMap']['searchString'] : null;
        $dataProvider->pagination->params['OrderCatalogSearchMap[selectedVendor]'] = $selectedVendor;
        $dataProvider->pagination->params['OrderCatalogSearchMap[selectedCategory]'] = $selectedCategory;
        $dataProvider->pagination->params['OrderCatalogSearchMap[service_id]'] = $service;

        //$cart = (new CartWebApi())->items(); //$client->getCart();
        // Вывод по 10
        $dataProvider->pagination->pageSize = 10;

        $services = ['0' => 'Выберите сервис'];
        $lic0 = $client->getLicenseList();
        if ((isset($lic0['rkws'])) && (isset($lic0['rkws_ucs']))) {
            $services = $services + ['1' => 'R-keeper'];
        }
        if (isset($lic0['iiko'])) {
            $services = $services + ['2' => 'iiko'];
        }
        if (isset($lic0['odinsobsh'])) {
            $services = $services + ['8' => '1С-Ресторан'];
        }
        if (isset($lic0['tillypad'])) {
            $services = $services + ['10' => 'Tillypad'];
        }

        $vi = 'index';

        $stores = AllMaps::getStoreListService($searchModel->service_id, $client->id);
        if ($service == Registry::IIKO_SERVICE_ID) {
            $mainOrg = iikoService::getMainOrg($client->id);
            if ($orgId == $mainOrg) {
                $editCan = 1;
            } else {
                if (($selectedVendor != null) && ($selectedVendor != 0)) {
                    $vendorIsMain = RelationSuppRest::find()->where(['rest_org_id' => $mainOrg, 'supp_org_id' => $selectedVendor, 'status' => 1, 'deleted' => 0])->one();
                    ($vendorIsMain) ? $editCan = 0 : $editCan = 1;
                } else {
                    $editCan = 0;
                }
            }
        } else {
            $editCan = 1;
            $mainOrg = null;
        }

        if (Yii::$app->request->isPjax) {
            return $this->renderAjax($vi, compact('dataProvider', 'searchModel', 'client', 'cart', 'vendors', 'selectedVendor', 'stores', 'services', 'mainOrg', 'editCan'));
        } else {
            return $this->render($vi, compact('dataProvider', 'searchModel', 'client', 'cart', 'vendors', 'selectedVendor', 'stores', 'services', 'mainOrg', 'editCan'));
        }
    }

    public function actionEditkoef($service_id) // метод установки коэффициента для товаров
    {

        $prod_id = Yii::$app->request->post('editableKey');
        $koef_old = Yii::$app->request->post('koef');
        $koef = str_replace(',', '.', $koef_old);
        $koef = floor($koef * 1000000) / 1000000;
        $koef = round($koef, 6);
        $org_id = $this->currentUser->organization->id;

        $product = AllMaps::find()->where(['org_id' => $org_id, 'service_id' => $service_id, 'is_active' => 1, 'product_id' => $prod_id])->one();

        if (!empty($product)) {
            $product->koef = $koef;

            if (!$product->save()) {
                throw new NotFoundHttpException(Yii::t('error', 'api.allmaps.position.not.save', ['ru' => 'Сохранить позицию в глобальном сопоставлении не удалось.']));
            }
        } else {
            $product = new AllMaps();

            $product->service_id = $service_id;
            $product->org_id = $org_id;
            $product->product_id = $prod_id;
            $product->supp_id = CatalogBaseGoods::getSuppById($product->product_id);
            $product->is_active = 1;
            $product->koef = $koef;

            if (!$product->save()) {
                throw new NotFoundHttpException(Yii::t('error', 'api.allmaps.position.not.save', ['ru' => 'Сохранить позицию в глобальном сопоставлении не удалось.']));
            }
        }

        if ($service_id == Registry::IIKO_SERVICE_ID) {
            $mainOrg = iikoService::getMainOrg($org_id);
            if ($org_id == $mainOrg) {
                $obConstModel = iikoDicconst::findOne(['denom' => 'main_org']);
                $arChildsModels = iikoPconst::find()->select('org')->where(['const_id' => $obConstModel->id, 'value' => $mainOrg])->all();
                if ($arChildsModels) {
                    foreach ($arChildsModels as $child) {
                        $product = AllMaps::find()->where(['org_id' => $child->org, 'service_id' => $service_id, 'is_active' => 1, 'product_id' => $prod_id])->one();
                        if (!empty($product)) {
                            if ($product->koef == 1) {
                                $product->koef = $koef;

                                if (!$product->save()) {
                                    throw new NotFoundHttpException(Yii::t('error', 'api.allmaps.position.not.save', ['ru' => 'Сохранить позицию в глобальном сопоставлении не удалось.']));
                                }
                            }
                        } else {
                            $product = new AllMaps();

                            $product->service_id = $service_id;
                            $product->org_id = $child->org;
                            $product->product_id = $prod_id;
                            $product->supp_id = CatalogBaseGoods::getSuppById($product->product_id);
                            $product->is_active = 1;
                            $product->koef = $koef;

                            if (!$product->save()) {
                                throw new NotFoundHttpException(Yii::t('error', 'api.allmaps.position.not.save', ['ru' => 'Сохранить позицию в глобальном сопоставлении не удалось.']));
                            }
                        }
                    }
                }
            }
        }
        $koef_temp = $koef * 1000000;
        $koef_len = strlen($koef_temp);
        $koef_left = substr($koef_temp, 0, $koef_len - 6);
        if ($koef_left == '') {
            $koef_left = '0';
        }
        $koef_right = substr($koef_temp, $koef_len - 6);
        $res = $koef_left . ',' . $koef_right;
        return Json::encode(['output' => $res, 'message' => '']);
    }

    public function actionEditstore($service_id) // метод установки склада для товаров
    {

        $prod_id = Yii::$app->request->post('editableKey');
        $store = Yii::$app->request->post('store');
        $org_id = $this->currentUser->organization->id;

        $product = AllMaps::find()->where(['org_id' => $org_id, 'service_id' => $service_id, 'is_active' => 1, 'product_id' => $prod_id])->one();

        if (!empty($product)) { // Product link already mapped in table
            $product->store_rid = $store;

            if (!$product->save()) {
                throw new NotFoundHttpException(Yii::t('error', 'api.allmaps.position.not.save', ['ru' => 'Сохранить позицию в глобальном сопоставлении не удалось.']));
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
                throw new NotFoundHttpException(Yii::t('error', 'api.allmaps.position.not.save', ['ru' => 'Сохранить позицию в глобальном сопоставлении не удалось.']));
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
        $product = AllMaps::find()->where(['org_id' => $org_id, 'service_id' => $service_id, 'is_active' => 1, 'product_id' => $prod_id])->one();

        if (!empty($product)) { // Product link already mapped in table
            $product->vat = $vat;

            if (!$product->save()) {
                throw new NotFoundHttpException(Yii::t('error', 'api.allmaps.position.not.save', ['ru' => 'Сохранить позицию в глобальном сопоставлении не удалось.']));
            }
        } else { // New link for mapping creation
            $product = new AllMaps();
            $product->service_id = $service_id;
            $product->org_id = $this->currentUser->organization->id;
            $product->product_id = $prod_id;
            $product->supp_id = CatalogBaseGoods::getSuppById($prod_id);
            $product->is_active = 1;
            $product->vat = $vat;

            if (!$product->save()) {
                throw new NotFoundHttpException(Yii::t('error', 'api.allmaps.position.not.save', ['ru' => 'Сохранить позицию в глобальном сопоставлении не удалось.']));
            }
        }

        if ($service_id == Registry::IIKO_SERVICE_ID) {
            $childs = iikoService::getChildOrgsId($org_id);
            if (!empty($childs)) {
                foreach ($childs as $child) {
                    $child_product = AllMaps::find()->where(['org_id' => $child, 'service_id' => $service_id, 'is_active' => 1, 'product_id' => $prod_id])->one();
                    if (!empty($child_product)) {
                        if ($child_product->vat === null) {
                            $child_product->vat = $vat;
                        }
                        if (!$child_product->save()) {
                            throw new NotFoundHttpException(Yii::t('error', 'api.allmaps.position.not.save', ['ru' => 'Сохранить позицию в глобальном сопоставлении не удалось.']));
                        }
                    } else {
                        $child_product = new AllMaps();
                        $child_product->vat = $vat;
                        $child_product->service_id = $service_id;
                        $child_product->org_id = $child;
                        $child_product->product_id = $prod_id;
                        $child_product->supp_id = CatalogBaseGoods::getSuppById($prod_id);
                        $child_product->serviceproduct_id = $product->serviceproduct_id;
                        $child_product->unit_rid = null;
                        $child_product->store_rid = null;
                        $child_product->koef = $product->koef ?? 1;
                        $child_product->is_active = 1;
                        if (!$child_product->save()) {
                            throw new NotFoundHttpException(Yii::t('error', 'api.allmaps.position.not.save', ['ru' => 'Сохранить позицию в глобальном сопоставлении не удалось.']));
                        }
                    }
                }
            }
        }

        $res = $product->vat;
        return Json::encode(['output' => $res, 'message' => '']);
    }

    public function getLastUrl() // метод получения предыдущего URL
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

    public function actionSaveSelectedMaps() // метод сохранения изменений выделения "флажками" товаров
    {
        $selected = Yii::$app->request->get('selected');
        $state = Yii::$app->request->get('state');

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

    protected function findModel($id) // метод нахождения одной товарной позиции
    {
        if (($model = AllMaps::findOne(['product_id' => $id])) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('error', 'api.allmaps.position.not.save', ['ru' => 'Позиции с таким номером в глобальном сопоставлении не существует.']));
        }
    }

    public function actionAutoCompleteSelectedProducts() // метод подстановки значений в сопоставлении товаров при вводе букв названия товара для доступных товаров
    {
        $orgId = User::findOne(Yii::$app->user->id)->organization_id;
        $constId = iikoDicconst::findOne(['denom' => 'main_org']);
        $parentId = iikoPconst::findOne(['const_id' => $constId->id, 'org' => $orgId]);

        $organizationID = (isset($parentId, $parentId->value) && strlen((int)$parentId->value) ==
            strlen($parentId->value) && $parentId->value > 0) ? $parentId->value : $orgId;

        $result = iikoSelectedProduct::find()->where('organization_id = :w_org', [':w_org' => $organizationID])->count();
        return $result;
    }

    public function actionAutoCompleteNew() // метод подстановки значений в сопоставлении товаров при вводе букв названия товара
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

    public function actionEditNew() // метод сопоставления товаров
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
                $child_product = AllMaps::find()->select('id, store_rid, vat, koef')->where(['org_id' => $child->org, 'service_id' => Registry::IIKO_SERVICE_ID, 'product_id' => $main_product->product_id])->one();
                if ($child_product) {
                    $childProduct = AllMaps::findOne($child_product->id);
                    (is_null($child_product->store_rid)) ? $childProduct->store_rid = null : $childProduct->store_rid = $child_product->store_rid;
                    (is_null($child_product->vat)) ? $childProduct->vat = null : $childProduct->vat = $child_product->vat;
                    ($child_product->koef == 1) ? $childProduct->koef = $main_product->koef : $childProduct->koef = $child_product->koef;
                } else {
                    $childProduct = new AllMaps();
                    $childProduct->store_rid = null;
                    $childProduct->vat = $main_product->vat;
                    $childProduct->koef = $main_product->koef;
                }
                $childProduct->service_id = $main_product->service_id;
                $childProduct->org_id = $child->org;
                $childProduct->product_id = $main_product->product_id;
                $childProduct->supp_id = $main_product->supp_id;
                $childProduct->serviceproduct_id = $main_product->serviceproduct_id;
                $childProduct->is_active = $main_product->is_active;
                if (!is_null($childProduct->serviceproduct_id)) {
                    $childProduct->linked_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
                }
                try {
                    if (!$childProduct->save()) {
                        throw new NotFoundHttpException(Yii::t('error', 'api.iiko.child.product.not.save', ['ru' => 'Не удалось сохранить продукт дочернего бизнеса.']));
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
        $main_product = AllMaps::find()->select('service_id, product_id, supp_id, serviceproduct_id, koef, vat, is_active')->where(['org_id' => $parent_id, 'service_id' => Registry::IIKO_SERVICE_ID, 'product_id' => $product_id])->one();
        foreach ($arChildsModels as $child) {
            $child_product = AllMaps::find()->select('id, store_rid, vat, koef')->where(['org_id' => $child->org, 'service_id' => Registry::IIKO_SERVICE_ID, 'product_id' => $main_product->product_id])->one();
            if ($child_product) {
                $childProduct = AllMaps::findOne($child_product->id);
                (is_null($child_product->store_rid)) ? $childProduct->store_rid = null : $childProduct->store_rid = $child_product->store_rid;
                (is_null($child_product->vat)) ? $childProduct->vat = null : $childProduct->vat = $child_product->vat;
                ($child_product->koef == 1) ? $childProduct->koef = $main_product->koef : $childProduct->koef = $child_product->koef;
            } else {
                $childProduct = new AllMaps();
                $childProduct->store_rid = null;
                $childProduct->vat = $main_product->vat;
            }
            $childProduct->service_id = $main_product->service_id;
            $childProduct->org_id = $child->org;
            $childProduct->product_id = $main_product->product_id;
            $childProduct->supp_id = $main_product->supp_id;
            $childProduct->serviceproduct_id = $main_product->serviceproduct_id;
            $childProduct->is_active = $main_product->is_active;
            if (!is_null($childProduct->serviceproduct_id)) {
                $childProduct->linked_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
            }
            try {
                if (!$childProduct->save()) {
                    throw new NotFoundHttpException(Yii::t('error', 'api.iiko.child.product.not.save', ['ru' => 'Не удалось сохранить продукт дочернего бизнеса.']));
                }
            } catch (\Exception $e) {
                \yii::error('Не удалось сохранить продукт ' . $main_product->id . ' дочернего бизнеса ' . $child->org);
                return false;
            }
        }
        return true;
    }

    public function actionApplyFullmapNew() // метод установки данных для всех товарных позиций, отмеченных "флажками"
    {

        $koef = Yii::$app->request->post('koef_set');
        $store = Yii::$app->request->post('store_set');
        $vat = Yii::$app->request->post('vat_set');
        $service_id = Yii::$app->request->post('service_set');
        $spisok_string = Yii::$app->request->post('spisok');

        $koef = 0 + str_replace(',', '.', $koef);
        $koef_end = round($koef, 4);
        $spisok = explode(',', $spisok_string);
        $org_id = Organization::findOne(User::findOne(Yii::$app->user->id)->organization_id)->id;

        foreach ($spisok as $tovar) {
            $product = AllMaps::find()->where(['org_id' => $org_id, 'service_id' => $service_id, 'is_active' => 1, 'product_id' => $tovar])->one();
            if ($product) {
                if ($store != -1) {
                    $product->store_rid = $store;
                }

                if ($vat != -1) {
                    $product->vat = $vat;
                }

                if ($koef != -1) {
                    $product->koef = $koef_end;
                }

                if (!$product->save()) {
                    throw new NotFoundHttpException(Yii::t('error', 'api.allmaps.position.not.save', ['ru' => 'Сохранить позицию в глобальном сопоставлении не удалось.']));
                }
            } else {
                $product = new AllMaps();

                $product->service_id = $service_id;
                $product->org_id = $org_id;
                $product->product_id = $tovar;
                $product->supp_id = CatalogBaseGoods::getSuppById($product->product_id);
                $product->is_active = 1;
                $product->serviceproduct_id = null;
                $product->unit_rid = null;

                ($store != -1) ? $product->store_rid = $store : $product->store_rid = null;
                ($vat != -1) ? $product->vat = $vat : $product->vat = null;
                ($koef != -1) ? $product->koef = $koef_end : $product->koef = 1;

                if (!$product->save()) {
                    throw new NotFoundHttpException(Yii::t('error', 'api.allmaps.position.not.save', ['ru' => 'Сохранить позицию в глобальном сопоставлении не удалось.']));
                }
            }
        }
        if (($service_id == Registry::IIKO_SERVICE_ID) and ($koef != -1)) {
            $mainOrg = iikoService::getMainOrg($org_id);
            if ($mainOrg == $org_id) {
                $obConstModel = iikoDicconst::findOne(['denom' => 'main_org']);
                $arChildsModels = iikoPconst::find()->select('org')->where(['const_id' => $obConstModel->id, 'value' => $org_id])->all();
                foreach ($spisok as $tovar) {
                    $product = AllMaps::find()->where(['org_id' => $org_id, 'service_id' => $service_id, 'is_active' => 1, 'product_id' => $tovar])->one();
                    foreach ($arChildsModels as $child) {
                        $child_product = AllMaps::find()->select('id, store_rid, vat, koef')->where(['org_id' => $child->org, 'service_id' => Registry::IIKO_SERVICE_ID, 'product_id' => $product->product_id])->one();
                        if ($child_product) {
                            $childProduct = AllMaps::findOne($child_product->id);
                            (is_null($child_product->store_rid)) ? $childProduct->store_rid = null : $childProduct->store_rid = $child_product->store_rid;
                            (is_null($child_product->vat)) ? $childProduct->vat = null : $childProduct->vat = $child_product->vat;
                            ($child_product->koef == 1) ? $childProduct->koef = $product->koef : $childProduct->koef = $child_product->koef;
                        } else {
                            $childProduct = new AllMaps();
                            $childProduct->store_rid = null;
                            $childProduct->vat = $product->vat;
                            $childProduct->koef = 1;
                        }
                        $childProduct->service_id = $product->service_id;
                        $childProduct->org_id = $child->org;
                        $childProduct->product_id = $product->product_id;
                        $childProduct->supp_id = $product->supp_id;
                        $childProduct->serviceproduct_id = $product->serviceproduct_id;
                        $childProduct->is_active = $product->is_active;
                        if (!is_null($childProduct->serviceproduct_id)) {
                            $childProduct->linked_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
                        }
                        try {
                            if (!$childProduct->save()) {
                                throw new \Exception('Не удалось сохранить продукт дочернего бизнеса.');
                            }
                        } catch (\Exception $e) {
                            throw new NotFoundHttpException(Yii::t('error', 'api.iiko.child.product.not.save', ['ru' => 'Не удалось сохранить продукт дочернего бизнеса.']));
                            return false;
                        }
                    }

                }
            }
        }

        $res_name = '';
        if ($store != -1) {
            switch ($service_id) {
                case Registry::RK_SERVICE_ID :
                    $res = RkStoretree::find()->where(['id' => $store])->one();
                    $res_name = $res->name;
                    break;
                case Registry::IIKO_SERVICE_ID :
                    $res = \api\common\models\iiko\iikoStore::find()->where(['id' => $store])->one();
                    $res_name = $res->denom;
                    break;
                case Registry::ONE_S_CLIENT_SERVICE_ID :
                    $res = OneSStore::find()->where(['id' => $store])->one();
                    $res_name = $res->name;
                    break;
                case Registry::TILLYPAD_SERVICE_ID :
                    $res = \api\common\models\iiko\iikoStore::find()->where(['id' => $store])->one();
                    $res_name = $res->denom;
                    break;
            }
        }
        return $res_name;
    }

}




