<?php

namespace frontend\modules\clientintegr\modules\iiko\controllers;

use api\common\models\iiko\iikoDicconst;
use api\common\models\iiko\iikoPconst;
use api\common\models\iiko\iikoSelectedProduct;
use api\common\models\iiko\iikoSelectedStore;
use api\common\models\iiko\iikoService;
use api\common\models\iiko\search\iikoDicconstSearch;
use Yii;

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

        if (isset($post['selection'])) {
            $products = $post['selection'];
            $allSelectedProducts = iikoSelectedProduct::findAll(['organization_id' => $org]);
            foreach ($allSelectedProducts as &$product) {
                if (!in_array($product->product_id, $products)) {
                    $product->delete();
                }
            }
            foreach ($products as $productID) {
                $selectedProduct = iikoSelectedProduct::findOne(['product_id' => $productID, 'organization_id' => $org]);
                if (!$selectedProduct) {
                    $selectedProduct = new iikoSelectedProduct();
                    $selectedProduct->product_id = $productID;
                    $selectedProduct->organization_id = $org;
                    $selectedProduct->save();
                }
            }
            $post['iikoPconst']['value'] = iikoSelectedProduct::find()->where(['organization_id' => $org])->count();
        } else {
            if (isset($post['selected_goods'])) {
                $allSelectedProducts = iikoSelectedProduct::findAll(['organization_id' => $org]);
                foreach ($allSelectedProducts as &$product) {
                    $product->delete();
                }
                $post['iikoPconst']['value'] = iikoSelectedProduct::find()->where(['organization_id' => $org])->count();
            }
        }

        if (isset($post['Stores'])) {
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
            $post['iikoPconst']['value'] = iikoSelectedStore::find()->where(['organization_id' => $org])->count();
        }

        if ($pConst->load($post) && $pConst->save()) {
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
                'id' => $id
            ]);
        }

    }


    public function actionAjaxAddProductToSession()
    {
        $productID = Yii::$app->request->post('productID');
        $session = Yii::$app->session;
        $session['SelectedProduct.' . $productID] = $productID;
    }
}
