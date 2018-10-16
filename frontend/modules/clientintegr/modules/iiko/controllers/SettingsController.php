<?php

namespace frontend\modules\clientintegr\modules\iiko\controllers;

use api\common\models\iiko\iikoDicconst;
use api\common\models\iiko\iikoPconst;
use api\common\models\iiko\iikoSelectedProduct;
use api\common\models\iiko\iikoSelectedStore;
use api\common\models\iiko\iikoService;
use api\common\models\iiko\search\iikoDicconstSearch;
use common\helpers\ModelsCollection;
use common\models\Role;
use common\models\User;
use Yii;
use yii\data\ArrayDataProvider;
use yii\web\Response;

class SettingsController extends \frontend\modules\clientintegr\controllers\DefaultController
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new iikoDicconstSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $lic = iikoService::getLicense();
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

    /**
     * @param $id
     * @return string|\yii\web\Response
     */
    public function actionChangeConst($id)
    {
        $org = Yii::$app->user->identity->organization_id;
        $pConst = iikoPconst::findOne(['const_id' => $id, 'org' => $org]);

        if (empty($pConst)) {
            $pConst = new iikoPconst();
            $pConst->org = $org;
            $pConst->const_id = $id;
            if (!$pConst->save()) {
                echo "Can't create P Const model (";
                die();
            }
        }

        $lic = iikoService::getLicense();
        $vi = $lic ? 'update' : '/default/_nolic';

        $post = Yii::$app->request->post();
        if (isset($post['sort'])) {
            $sort = $post['sort'];
        } else {
            $sort = 'denom';
        }
        if (isset($post['page'])) {
            $page = $post['page'];
        } else {
            $page = 1;
        }
        if (isset($post['iikoProductSearch']['product_type'])) {
            $productSearch = $post['iikoProductSearch']['product_type'];
        } else {
            $productSearch = 'all';
        }
        if (isset($post['iikoProductSearch']['cooking_place_type'])) {
            $cookingPlaceSearch = $post['iikoProductSearch']['cooking_place_type'];
        } else {
            $cookingPlaceSearch = 'all';
        }
        if (isset($post['iikoProductSearch']['unit'])) {
            $unitSearch = $post['iikoProductSearch']['unit'];
        } else {
            $unitSearch = 'all';
        }

        if (isset($post['selection']) || isset($post['selected_goods'])) {
            $post['iikoPconst']['value'] = $this->handleSelectedProducts($post, $org);
        }

        if (isset($post['Stores'])) {
            $post['iikoPconst']['value'] = $this->handleSelectedStores($post, $org);
        }

        if ($pConst->load($post) && $pConst->save() && $id != 7) {
            if ($pConst->getErrors()) {
                var_dump($pConst->getErrors());
                exit;
            }
            return $this->redirect(['index']);
        } else {
            $dicConst = iikoDicconst::findOne(['id' => $pConst->const_id]);
            return $this->render($vi, [
                'model' => $pConst,
                'dicConst' => $dicConst,
                'id' => $id,
                'sort' => $sort,
                'productSearch' => $productSearch,
                'cookingPlaceSearch' => $cookingPlaceSearch,
                'unitSearch' => $unitSearch,
                'page' => $page,
            ]);
        }

    }


    private function handleSelectedProducts($post, $org)
    {
        if (isset($post['goods'])) {
            $products = $post['goods'];
            $allSelectedProducts = iikoSelectedProduct::findAll(['organization_id' => $org]);
            foreach ($allSelectedProducts as $product) {
                if (isset($products[$product->product_id]) && $products[$product->product_id] == 0) {
                    $product->delete();
                }
            }
            foreach ($products as $productID => $value) {
                if ($value == 0) {
                    continue;
                }
                $selectedProduct = iikoSelectedProduct::findOne(['product_id' => $productID, 'organization_id' => $org]);
                if (!$selectedProduct) {
                    $selectedProduct = new iikoSelectedProduct();
                    $selectedProduct->product_id = $productID;
                    $selectedProduct->organization_id = $org;
                    //$selectedProduct->save();
                    if (!$selectedProduct->save()) {
                        var_dump($selectedProduct->getErrors()); die();
                    }
                }
            }
            $count = iikoSelectedProduct::find()->where(['organization_id' => $org])->count();
        } else {
            if (isset($post['selected_goods'])) {
                $allSelectedProducts = iikoSelectedProduct::findAll(['organization_id' => $org]);
                foreach ($allSelectedProducts as &$product) {
                    $product->delete();
                }
                $count = iikoSelectedProduct::find()->where(['organization_id' => $org])->count();
            }
        }
        return $count;
    }


    private function handleSelectedStores($post, $org)
    {
        $stores = $post['Stores'];
        foreach ($stores as $storeID => $selected) {
            $selectedStore = iikoSelectedStore::findOne(['store_id' => $storeID, 'organization_id' => $org]);
            if ($selected == '1') {
                if (!$selectedStore) {
                    $selectedStore = new iikoSelectedStore();
                    $selectedStore->store_id = $storeID;
                    $selectedStore->organization_id = $org;
                    $selectedStore->save();
                }
            } else {
                if ($selectedStore) {
                    $selectedStore->delete();
                }
            }
        }
        return iikoSelectedStore::find()->where(['organization_id' => $org])->count();
    }


    public function actionAjaxAddProductToSession()
    {
        $productID = Yii::$app->request->post('productID');
        $session = Yii::$app->session;
        $session['SelectedProduct.' . $productID] = $productID;
    }

    /**
     * Render collation table
     * @var iikoPconst->const_id $const_id
     */
    public function actionCollations()
    {
        $obConstModel = iikoDicconst::findOne(['denom' => 'main_org']);
        /** @var $currentUser User */
        $currentUser = User::findIdentity(Yii::$app->user->id);
        $currentUserRole = User::findOne(Yii::$app->user->id);
        /**@var $roles array Available roles ids */
        $roles = [Role::ROLE_RESTAURANT_MANAGER, Role::ROLE_ADMIN, Role::ROLE_SUPPLIER_MANAGER, Role::ROLE_FRANCHISEE_LEADER];
        if (in_array($currentUserRole->role_id, $roles)) {
            $arOrgsObj = $currentUser->getAllOrganization();
            $provider = new ArrayDataProvider([
                'allModels' => $arOrgsObj,
                'pagination' => [
                    'pageSize' => 999,
                ],
                'key' => 'id'
            ]);

            $arIdsOrgs = [];
            foreach ($arOrgsObj as $org) {
                $arIdsOrgs[] = $org->id;
            }

            $pConst = iikoPconst::findOne(['const_id' => $obConstModel->id, 'org' => $arIdsOrgs]);

            return $this->render('collations', [
                    'provider' => $provider,
                    'parentId' => $pConst,
                ]
            );
        }

        return $this->redirect('index');
    }

    /**
     * Создаем сопоставления в дочерних бизнесах
     * @var iikoPconst->const_id $const_id
     * @return array
     */
    public function actionApplyCollation()
    {
        $obConstModel = iikoDicconst::findOne(['denom' => 'main_org']);
        Yii::$app->response->format = Response::FORMAT_JSON;
        $ids = Yii::$app->request->post('ids');
        $mainId = Yii::$app->request->post('main');
        $arModels = [];

        $arPconstModels = iikoPconst::find()->select('org')->where(['const_id' => $obConstModel->id, 'org' => $ids])->indexBy('org')->all();
        $arDeletedIds = array_keys($arPconstModels);

        foreach ($ids as $id) {
            $pConst = new iikoPconst();
            $pConst->org = $id;
            $pConst->const_id = $obConstModel->id;
            $pConst->value = $mainId;
            $arModels[] = $pConst;
        }

        if (!empty($arDeletedIds)) {
            $resDel = $this->actionCancelCollation($arDeletedIds);
        }

        if (empty($arModels) && !empty($arDeletedIds)) {
            return $resDel;
        } elseif (empty($arModels) && empty($arDeletedIds)) {
            return ['success' => false, 'error' => 'Невозможно выполнить данную операцию'];
        }

        $modelCollection = new ModelsCollection();

        return $modelCollection->saveMultiple($arModels);
    }

    /**
     * Удаляем сопоставления в дочерних бизнесах
     *
     * @var iikoPconst->const_id $const_id
     * @var array $ids for delete
     * @return array
     */
    public function actionCancelCollation($ids = null)
    {
        $obConstModel = iikoDicconst::findOne(['denom' => 'main_org']);
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (is_null($ids)) {
            $ids = Yii::$app->request->post('ids');
        }
        try {
            $pConst = iikoPconst::deleteAll(['const_id' => $obConstModel->id, 'org' => $ids]);
        } catch (\Throwable $throwable) {
            return ['success' => false, 'error' => $throwable->getMessage()];
        }

        return ['success' => true];
    }
}
