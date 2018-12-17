<?php

namespace api_web\modules\integration\modules\one_s\models;

use api\common\models\one_s\one_sWaybillData;
use yii\db\Transaction;
use yii\web\BadRequestHttpException;
use api\common\models\one_s\one_sAgent;
use api\common\models\one_s\one_sCategory;
use api\common\models\one_s\one_sDic;
use api\common\models\one_s\one_sDictype;
use api\common\models\one_s\one_sProduct;
use api\common\models\one_s\one_sStore;
use api_web\components\WebApi;
use api_web\exceptions\ValidationException;
use frontend\modules\clientintegr\modules\one_s\helpers\one_sApi;

class one_sSync extends WebApi
{
    /**
     * Запуск синфронизации определенного типа
     * @param $type
     * @return array
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function run($type)
    {
        /**
         * @var $transaction Transaction
         */
        $model = one_sDictype::findOne($type);

        if (empty($model)) {
            throw new BadRequestHttpException('Not found type ' . $type);
        }

        if (empty($model->method)) {
            throw new BadRequestHttpException('Empty [iko_dictype.method] in DB');
        }

        if (method_exists($this, $model->method) === true) {
            $transaction = \Yii::$app->get('db_api')->beginTransaction();
            try {
                //Пробуем пролезть в iko
                if (!one_sApi::getInstance()->auth()) {
                    throw new BadRequestHttpException('Не удалось авторизоваться в one_s - Office');
                }
                //Синхронизируем нужное нам и
                //ответ получим, сколько записей у нас в боевом состоянии
                $count = $this->{$model->method}();
                //Убиваем сессию, а то закончатся на сервере one_s
                one_sApi::getInstance()->logout();
                //Обновляем данные
                $dicModel = one_sDic::findOne(['dictype_id' => $model->id, 'org_id' => $this->user->organization->id]);
                if (!$dicModel->updateSuccessSync($count)) {
                    throw new BadRequestHttpException($dicModel->getFirstErrors());
                }
                //Сохраняем данные
                $transaction->commit();
                return ['success' => true];
            } catch (\Exception $e) {
                $transaction->rollBack();
                one_sApi::getInstance()->logout();
                one_sDic::errorSync($model->id);
                throw $e;
            }
        } else {
            throw new BadRequestHttpException('Not found method [one_sSync->' . $model->method . '()]');
        }
    }

    /**
     * Список справочников
     * @return array
     */
    public function list()
    {
        $result = [];
        $models = one_sDic::find()->where(['org_id' => $this->user->organization->id])->all();

        foreach ($models as $model) {
            $result[] = [
                'name' => $model->dictype->denom,
                'status' => $model->dicstatus->denom,
                'updated_at' => $model->updated_at,
                'obj_count' => $model->obj_count,
                'type' => $model->dictype_id
            ];
        }

        return $result;
    }

    /**
     * Синхронизация складов
     *
     * @return integer
     * @throws ValidationException
     */
    protected function store()
    {
        //Получаем список складов
        $stores = one_sApi::getInstance()->getStores();
        if (!empty($stores['corporateItemDto'])) {
            //поскольку мы не можем отследить изменения на стороне провайдера
            one_sStore::updateAll(['is_active' => 0], ['org_id' => $this->user->organization->id]);
            foreach ($stores['corporateItemDto'] as $store) {
                $model = one_sStore::findOne(['uuid' => $store['id'], 'org_id' => $this->user->organization->id]);
                //Если нет категории у нас, создаем
                if (empty($model)) {
                    $model = new one_sStore([
                        'uuid' => $store['id'],
                        'org_id' => $this->user->organization->id
                    ]);
                }
                $model->is_active = 1;
                if (!empty($store['name'])) {
                    $model->denom = $store['name'];
                }
                if (!empty($store['code'])) {
                    $model->store_code = $store['code'];
                }
                if (!empty($store['type'])) {
                    $model->store_type = $store['type'];
                }
                //Валидируем сохраняем
                if (!$model->validate() || !$model->save()) {
                    throw new ValidationException($model->getFirstErrors());
                }
            }
        }
        //Обновляем колличество полученных объектов
        return (int)one_sStore::find()->where(['is_active' => 1, 'org_id' => $this->user->organization->id])->count();
    }

    /**
     * Синхронизация контрагентов
     * @return int
     * @throws ValidationException
     */
    protected function agent()
    {
        $agents = one_sApi::getInstance()->getSuppliers();
        if (!empty($agents['employee'])) {
            //поскольку мы не можем отследить изменения на стороне провайдера
            one_sAgent::updateAll(['is_active' => 0], ['org_id' => $this->user->organization->id]);
            foreach ($agents['employee'] as $agent) {
                $model = one_sAgent::findOne(['uuid' => $agent['id'], 'org_id' => $this->user->organization->id]);
                //Если нет у нас, создаем
                if (empty($model)) {
                    $model = new one_sAgent(['uuid' => $agent['id']]);
                    $model->org_id = $this->user->organization->id;
                }
                $model->is_active = 1;
                $model->denom = $agent['name'];
                //Валидируем сохраняем
                if (!$model->validate() || !$model->save()) {
                    throw new ValidationException($model->getFirstErrors());
                }
            }
        }
        //Обновляем колличество полученных объектов
        return one_sAgent::find()->where(['is_active' => 1, 'org_id' => $this->user->organization->id])->count();
    }

    /**
     * Синхронизация категорий
     * @return int
     * @throws ValidationException
     */
    protected function category()
    {
        $items = one_sApi::getInstance()->getProducts();
        //Если пришли категории, обновляем их
        if (!empty($items['categories'])) {
            //Проставим признак всем категориям, что они не активны
            //поскольку мы не можем отследить изменения на стороне провайдера
            one_sCategory::updateAll(['is_active' => 0], ['org_id' => $this->user->organization->id]);
            foreach ($items['categories'] as $uuid => $category) {
                $model = one_sCategory::findOne(['uuid' => $uuid, 'org_id' => $this->user->organization->id]);
                //Если нет категории у нас, создаем
                if (empty($model)) {
                    $model = new one_sCategory([
                        'uuid' => $uuid,
                        'org_id' => $this->user->organization->id
                    ]);
                }
                $model->is_active = 1;
                //Родительская категория если есть
                if (!empty($category['parentId'])) {
                    $model->parent_uuid = $category['parentId'];
                }
                if (!empty($category['name'])) {
                    $model->denom = $category['name'];
                }
                if (!empty($category['productGroupType'])) {
                    $model->group_type = $category['productGroupType'];
                }
                if (!$model->validate() || !$model->save()) {
                    throw new ValidationException($model->getFirstErrors());
                }
            }
        }
        //Обновляем колличество полученных объектов
        return one_sCategory::find()->where(['is_active' => 1, 'org_id' => $this->user->organization->id])->count();
    }

    /**
     * Синхронизация продуктов
     * @return int
     * @throws ValidationException
     */
    protected function goods()
    {
        $items = one_sApi::getInstance()->getProducts();
        //Если пришли продукты, обновляем их
        if (!empty($items['products'])) {
            //поскольку мы не можем отследить изменения на стороне провайдера
            one_sProduct::updateAll(['is_active' => 0], ['org_id' => $this->user->organization->id]);
            foreach ($items['products'] as $uuid => $item) {
                $model = one_sProduct::findOne(['uuid' => $uuid, 'org_id' => $this->user->organization->id]);
                //Если нет категории у нас, создаем
                if (empty($model)) {
                    $model = new one_sProduct(['uuid' => $uuid]);
                    $model->org_id = $this->user->organization->id;
                }
                //Родительская категория если есть
                if (isset($item['parentId'])) {
                    $model->parent_uuid = $item['parentId'];
                }
                $model->is_active = 1;
                if (!empty($item['name'])) {
                    $model->denom = $item['name'];
                }
                if (!empty($item['productType'])) {
                    $model->product_type = $item['productType'];
                }
                if (!empty($item['mainUnit'])) {
                    $model->unit = $item['mainUnit'];
                }
                if (!empty($item['num'])) {
                    $model->num = $item['num'];
                }
                if (!empty($item['cookingPlaceType'])) {
                    $model->cooking_place_type = $item['cookingPlaceType'];
                }
                if (isset($item['containers'])) {
                    $model->containers = \GuzzleHttp\json_encode($item['containers']);
                }
                //Валидируем сохраняем
                if (!$model->validate() || !$model->save()) {
                    throw new ValidationException($model->getErrors());
                }
            }
        }
        //Обновляем колличество полученных объектов
        return one_sProduct::find()->where(['is_active' => 1, 'org_id' => $this->user->organization->id])->count();
    }

}