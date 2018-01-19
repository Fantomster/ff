<?php

namespace frontend\modules\clientintegr\modules\iiko\controllers;

use api\common\models\iiko\iikoAgent;
use api\common\models\iiko\iikoCategory;
use api\common\models\iiko\iikoDic;
use api\common\models\iiko\iikoDictype;
use api\common\models\iiko\iikoProduct;
use api\common\models\iiko\iikoStore;
use frontend\modules\clientintegr\modules\iiko\helpers\iikoApi;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\web\Response;

class SyncController extends \frontend\modules\clientintegr\controllers\DefaultController
{
    public $enableCsrfValidation = false;
    public $organisation_id;
    public $ajaxActions = ['goods-get', 'category-get', 'store-get', 'agent-get'];

    public function beforeAction($action)
    {
        $this->organisation_id = \Yii::$app->user->identity->organization_id;

        if(empty($this->organisation_id)) {
            return false;
        }

        if(in_array($this->action->id, $this->ajaxActions)) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            set_time_limit(3600);
        }

        return parent::beforeAction($action);
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => $this->ajaxActions,
                'rules' => [
                    [
                        'allow' => true,
                        'verbs' => ['POST'],
                        'matchCallback' => function () {
                            return \Yii::$app->request->isAjax;
                        },
                    ],
                ],
            ]
        ];
    }

    /**
     * @return string
     */
    public function actionGoodsView()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => iikoProduct::find()->where(['org_id' => $this->organisation_id])
        ]);

        return $this->render('goods-view', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @return string
     */
    public function actionCategoryView()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => iikoCategory::find()->where(['org_id' => $this->organisation_id])
        ]);

        return $this->render('category-view', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @return string
     */
    public function actionStoreView()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => iikoStore::find()->where(['org_id' => $this->organisation_id])
        ]);

        return $this->render('store-view', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @return string
     */
    public function actionAgentView()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => iikoAgent::find()->where(['org_id' => $this->organisation_id])
        ]);

        return $this->render('agent-view', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Синхронизация товаров
     * @return array
     */
    public function actionGoodsGet()
    {
        $id = \Yii::$app->request->post('id');
        $transaction = \Yii::$app->get('db_api')->beginTransaction();
        try {
            $api = iikoApi::getInstance();
            if ($api->auth()) {
                $dicModel = iikoDic::findOne($id);
                //Получаем список продуктов и категорий из АПИ
                //Хреновое апи = хреновой реализации!!!
                $items = $api->getProducts();
                //Отключаемся от АПИ
                $api->logout();
                //Проставим признак всем категориям, что они не активны
                //поскольку мы не можем отследить изменения на стороне провайдера
                iikoProduct::updateAll(['is_active' => 0], ['org_id' => $this->organisation_id]);
                //Если пришли категории, обновляем их
                if (!empty($items['products'])) {
                    foreach ($items['products'] as $uuid => $item) {
                        $model = iikoProduct::findOne(['uuid' => $uuid, 'org_id' => $this->organisation_id]);
                        //Если нет категории у нас, создаем
                        if (empty($model)) {
                            $model = new iikoProduct(['uuid' => $uuid]);
                            $model->org_id = $this->organisation_id;
                        }
                        //Родительская категория если есть
                        if (isset($item['parentId'])) {
                            $model->parent_uuid = $item['parentId'];
                        }
                        $model->is_active = 1;
                        $model->denom = $item['name'];
                        $model->product_type = $item['productType'];
                        $model->unit = $item['mainUnit'];
                        $model->num = $item['num'];
                        $model->code = $item['code'];

                        if(isset($item['cookingPlaceType'])) {
                            $model->cooking_place_type = $item['cookingPlaceType'];
                        }

                        if(isset($item['containers'])) {
                            $model->containers = \GuzzleHttp\json_encode($item['containers']);
                        }
                        //Валидируем сохраняем
                        if (!$model->validate() || !$model->save()) {
                            throw new \Exception(print_r($model->getFirstErrors(), 1));
                        }
                    }
                }
                //Обновляем колличество полученных объектов
                $count = iikoProduct::find()->where(['is_active' => 1, 'org_id' => $dicModel->org_id])->count();
                if (!$dicModel->updateSuccessSync($count)) {
                    throw new \Exception(print_r($dicModel->getFirstErrors(), 1));
                }
                //Сохраняем изменения
                $transaction->commit();
                return ['success' => true];
            } else {
                throw new \Exception('Не удалось авторизоваться');
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            iikoDic::errorSync($id);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Синхронизация категорий организации
     * @return array
     */
    public function actionCategoryGet()
    {
        $id = \Yii::$app->request->post('id');
        $transaction = \Yii::$app->get('db_api')->beginTransaction();
        try {
            $api = iikoApi::getInstance();
            if ($api->auth()) {
                $dicModel = iikoDic::findOne($id);
                //Получаем список продуктов и категорий из АПИ
                //Хреновое апи = хреновой реализации!!!
                $items = $api->getProducts();
                //Отключаемся от АПИ
                $api->logout();
                //Проставим признак всем категориям, что они не активны
                //поскольку мы не можем отследить изменения на стороне провайдера
                iikoCategory::updateAll(['is_active' => 0], ['org_id' => $this->organisation_id]);
                //Если пришли категории, обновляем их
                if (!empty($items['categories'])) {
                    foreach ($items['categories'] as $uuid => $category) {
                        $model = iikoCategory::findOne(['uuid' => $uuid, 'org_id' => $this->organisation_id]);
                        //Если нет категории у нас, создаем
                        if (empty($model)) {
                            $model = new iikoCategory(['uuid' => $uuid]);
                            $model->org_id = $this->organisation_id;
                        }
                        //Родительская категория если есть
                        if (isset($category['parentId'])) {
                            $model->parent_uuid = $category['parentId'];
                        }
                        $model->is_active = 1;
                        $model->denom = $category['name'];
                        $model->group_type = $category['productGroupType'];

                        if (!$model->validate() || !$model->save()) {
                            throw new \Exception(print_r($model->getFirstErrors(), 1));
                        }
                    }
                }

                //Обновляем колличество полученных объектов
                $count = iikoCategory::find()->where(['is_active' => 1, 'org_id' => $dicModel->org_id])->count();
                if (!$dicModel->updateSuccessSync($count)) {
                    throw new \Exception(print_r($dicModel->getFirstErrors(), 1));
                }
                //Сохраняем изменения
                $transaction->commit();
                return ['success' => true];
            } else {
                throw new \Exception('Не удалось авторизоваться');
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            iikoDic::errorSync($id);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Синхронизация складов
     * @return array|mixed
     */
    public function actionStoreGet()
    {
        $id = \Yii::$app->request->post('id');
        $transaction = \Yii::$app->get('db_api')->beginTransaction();
        try {
            $api = iikoApi::getInstance();
            if ($api->auth()) {
                $dicModel = iikoDic::findOne($id);
                //Получаем список складов
                $stores = $api->getStores();
                //Отключаемся от АПИ
                $api->logout();
                //Проставим признак всем категориям, что они не активны
                //поскольку мы не можем отследить изменения на стороне провайдера
                iikoStore::updateAll(['is_active' => 0], ['org_id' => $this->organisation_id]);
                //Если пришли категории, обновляем их
                if (!empty($stores['corporateItemDto'])) {
                    foreach ($stores['corporateItemDto'] as $store) {
                        $model = iikoStore::findOne(['uuid' => $store['id'], 'org_id' => $this->organisation_id]);
                        //Если нет категории у нас, создаем
                        if (empty($model)) {
                            $model = new iikoStore(['uuid' => $store['id']]);
                            $model->org_id = $this->organisation_id;
                        }
                        $model->is_active = 1;

                        if(!empty($store['name'])){
                            $model->denom = $store['name'];
                        }

                        if(!empty($store['code'])){
                            $model->store_code = $store['code'];
                        }
                        
                        if(!empty($store['type'])){
                            $model->store_type = $store['type'];
                        }

                        //Валидируем сохраняем
                        if (!$model->validate() || !$model->save()) {
                            throw new \Exception(print_r($model->getFirstErrors(), 1));
                        }
                    }
                }
                //Обновляем колличество полученных объектов
                $count = iikoStore::find()->where(['is_active' => 1, 'org_id' => $dicModel->org_id])->count();
                if (!$dicModel->updateSuccessSync($count)) {
                    throw new \Exception(print_r($dicModel->getFirstErrors(), 1));
                }
                //Сохраняем изменения
                $transaction->commit();
                return ['success' => true];
            } else {
                throw new \Exception('Не удалось авторизоваться');
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            iikoDic::errorSync($id);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Синхронизация контрагентов
     * @return array|mixed
     */
    public function actionAgentGet()
    {
        $id = \Yii::$app->request->post('id');
        $transaction = \Yii::$app->get('db_api')->beginTransaction();
        try {
            $api = iikoApi::getInstance();
            if ($api->auth()) {
                $dicModel = iikoDic::findOne($id);
                //Получаем список складов
                $agents = $api->getSuppliers();
                //Отключаемся от АПИ
                $api->logout();
                //Проставим признак всем категориям, что они не активны
                //поскольку мы не можем отследить изменения на стороне провайдера
                iikoAgent::updateAll(['is_active' => 0], ['org_id' => $this->organisation_id]);
                //Если пришли категории, обновляем их
                if (!empty($agents['employee'])) {
                    foreach ($agents['employee'] as $agent) {
                        $model = iikoAgent::findOne(['uuid' => $agent['id'], 'org_id' => $this->organisation_id]);
                        //Если нет у нас, создаем
                        if (empty($model)) {
                            $model = new iikoAgent(['uuid' => $agent['id']]);
                            $model->org_id = $this->organisation_id;
                        }
                        $model->is_active = 1;
                        $model->denom = $agent['name'];
                        //Валидируем сохраняем
                        if (!$model->validate() || !$model->save()) {
                            throw new \Exception(print_r($model->getFirstErrors(), 1));
                        }
                    }
                }
                //Обновляем колличество полученных объектов
                $count = iikoAgent::find()->where(['is_active' => 1, 'org_id' => $dicModel->org_id])->count();
                if (!$dicModel->updateSuccessSync($count)) {
                    throw new \Exception(print_r($dicModel->getFirstErrors(), 1));
                }
                //Сохраняем изменения
                $transaction->commit();
                return ['success' => true];
            } else {
                throw new \Exception('Не удалось авторизоваться');
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            iikoDic::errorSync($id);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
