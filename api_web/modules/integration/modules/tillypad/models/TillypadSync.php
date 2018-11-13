<?php

namespace api_web\modules\integration\modules\tillypad\models;

use api\common\models\iiko\iikoWaybillData;
use api_web\modules\integration\modules\tillypad\helpers\TillypadLogger;
use common\models\Journal;
use yii\db\Expression;
use yii\db\Transaction;
use yii\web\BadRequestHttpException;
use api\common\models\iiko\iikoAgent;
use api\common\models\iiko\iikoCategory;
use api\common\models\iiko\iikoDic;
use api\common\models\iiko\iikoDictype;
use api\common\models\iiko\iikoProduct;
use api\common\models\iiko\iikoStore;
use api_web\components\WebApi;
use api_web\exceptions\ValidationException;
use frontend\modules\clientintegr\modules\tillypad\helpers\TillypadApi;
use yii\web\Response;

class TillypadSync extends WebApi
{

    public $updates_uuid = [];

    /**
     * Запуск синхронизации определённого типа
     *
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
        $model = iikoDictype::findOne($type);

        if (empty($model)) {
            throw new BadRequestHttpException('Not found type ' . $type);
        }

        if (empty($model->method)) {
            throw new BadRequestHttpException('Empty [iko_dictype.method] in DB');
        }

        if (method_exists($this, $model->method) === true) {
            try {
                //Пробуем пролезть в Tillypad
                if (!TillypadApi::getInstance()->auth()) {
                    throw new BadRequestHttpException('Не удалось авторизоваться в Tillypad');
                }
                //Синхронизируем нужное нам и
                //ответ получим, сколько записей у нас в боевом состоянии
                $count = $this->{$model->method}();
                //Убиваем сессию, а то закончатся на сервере Tillypad
                TillypadApi::getInstance()->logout();
                //Обновляем данные
                $dicModel = iikoDic::findOne(['dictype_id' => $model->id, 'org_id' => $this->user->organization->id]);
                if (!$dicModel->updateSuccessSync($count)) {
                    throw new BadRequestHttpException($dicModel->getFirstErrors());
                }
                //Сохраняем данные
                return ['success' => true];
            } catch (\Exception $e) {
                TillypadApi::getInstance()->logout();
                iikoDic::errorSync($model->id);
                throw $e;
            }
        } else {
            throw new BadRequestHttpException('Not found method [TillypadSync->' . $model->method . '()]');
        }
    }

    /**
     * Список справочников
     *
     * @return array
     */
    public function list()
    {
        $result = [];
        $models = iikoDic::find()->where(['org_id' => $this->user->organization->id])->all();

        foreach ($models as $model) {
            $result[] = [
                'name'       => $model->dictype->denom,
                'status'     => $model->dicstatus->denom,
                'updated_at' => $model->updated_at,
                'obj_count'  => $model->obj_count,
                'type'       => $model->dictype_id
            ];
        }

        return $result;
    }

    /**
     * Синхронизация складов
     *
     * @return integer
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    protected function store()
    {
        //Получаем список складов
        $stores = TillypadApi::getInstance()->getStores();
        if (!empty($stores['corporateItemDto'])) {
            //поскольку мы не можем отследить изменения на стороне провайдера
            iikoStore::updateAll(['is_active' => 0], ['org_id' => $this->user->organization->id]);
            foreach ($stores['corporateItemDto'] as $store) {
                $model = iikoStore::findOne(['uuid' => $store['id'], 'org_id' => $this->user->organization->id]);
                //Если нет категории у нас, создаем
                if (empty($model)) {
                    $model = new iikoStore([
                        'uuid'   => $store['id'],
                        'org_id' => $this->user->organization->id
                    ]);
                }
                $model->is_active = 1;
                if (!empty($store['name'])) {
                    $model->denom = $store['name'];
                }
                if (!empty($store['code'])) {
                    $model->store_code = is_array($store['code']) ? implode('_', $store['code']) : (string)$store['code'];
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
        return (int)iikoStore::find()->where(['is_active' => 1, 'org_id' => $this->user->organization->id])->count();
    }

    /**
     * Синхронизация контрагентов
     *
     * @return int
     * @throws ValidationException
     */
    protected function agent()
    {
        $agents = TillypadApi::getInstance()->getSuppliers();
        if (!empty($agents['employee'])) {
            //поскольку мы не можем отследить изменения на стороне провайдера
            iikoAgent::updateAll(['is_active' => 0], ['org_id' => $this->user->organization->id]);
            foreach ($agents['employee'] as $agent) {
                $model = iikoAgent::findOne(['uuid' => $agent['id'], 'org_id' => $this->user->organization->id]);
                //Если нет у нас, создаем
                if (empty($model)) {
                    $model = new iikoAgent(['uuid' => $agent['id']]);
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
        return iikoAgent::find()->where(['is_active' => 1, 'org_id' => $this->user->organization->id])->count();
    }

    /**
     * Синхронизация категорий
     *
     * @return int
     * @throws ValidationException
     */
    protected function category()
    {
        $items = TillypadApi::getInstance()->getProducts();
        //Если пришли категории, обновляем их
        if (!empty($items['categories'])) {
            //Проставим признак всем категориям, что они не активны
            //поскольку мы не можем отследить изменения на стороне провайдера
            iikoCategory::updateAll(['is_active' => 0], ['org_id' => $this->user->organization->id]);
            foreach ($items['categories'] as $uuid => $category) {
                $model = iikoCategory::findOne(['uuid' => $uuid, 'org_id' => $this->user->organization->id]);
                //Если нет категории у нас, создаем
                if (empty($model)) {
                    $model = new iikoCategory([
                        'uuid'   => $uuid,
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
        return iikoCategory::find()->where(['is_active' => 1, 'org_id' => $this->user->organization->id])->count();
    }

    /**
     * Синхронизация продуктов
     *
     * @return int
     * @throws ValidationException
     */
    protected function goods()
    {
        $items = TillypadApi::getInstance()->getProducts();
        //Если пришли продукты, обновляем их
        if (!empty($items['products'])) {
            $org_id = $this->user->organization->id;
            //поскольку мы не можем отследить изменения на стороне провайдера
            iikoProduct::updateAll(['is_active' => 0], ['org_id' => $org_id]);

            $generator = function ($items) {
                foreach ($items as &$item) {
                    yield $item;
                }
            };

            foreach ($generator($items['products']) as $item) {
                $this->updateProduct($item['id'], $item);
            }

            if (!empty($this->updates_uuid)) {
                \Yii::$app->db_api->createCommand()
                    ->update(iikoProduct::tableName(), [
                        'is_active'  => 1,
                        'updated_at' => new Expression('NOW()')
                    ], ['uuid' => $this->updates_uuid])
                    ->execute();
            }
        }
        //Обновляем колличество полученных объектов
        return iikoProduct::find()->where(['is_active' => 1, 'org_id' => $this->user->organization->id])->count();
    }

    /**
     * Обновление продукта
     *
     * @param $uuid
     * @param $item
     * @return bool
     * @throws \Exception
     */
    private function updateProduct($uuid, $item)
    {
        $transaction = \Yii::$app->get('db_api')->beginTransaction();
        try {
            $org_id = $this->user->organization->id;
            $model = iikoProduct::findOne(['uuid' => $uuid, 'org_id' => $org_id]);
            //Если нет товара у нас, создаём
            if (empty($model)) {
                $model = new iikoProduct(['uuid' => $uuid]);
                $model->org_id = $org_id;
            }
            //Родительская категория если есть
            if (isset($item['parentId']) && !empty($item['parentId'])) {
                $model->parent_uuid = $item['parentId'];
            }
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
            if (isset($item['containers']) && !empty($item['containers'])) {
                $model->containers = \json_encode($item['containers']);
            }

            //Валидируем сохраняем
            if ($model->attributes !== $model->oldAttributes) {
                $model->is_active = 1;
                $model->save(false);
            } else {
                $this->updates_uuid[] = $uuid;
            }

            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->roolBack();
            throw $e;
        }
    }

    /**
     * Tillypad: Создание сопоставлений номенклатуры накладной с продуктами MixCart
     *
     * @param array $post
     * @return array
     */
    public function handleWaybillData(array $post): array
    {
        $waybillData = new iikoWaybillData();
        $waybillData->waybill_id = $post['waybill_id'];
        $waybillData->product_id = $post['product_id'];
        $waybillData->product_rid = $post['product_rid'];
        $waybillData->munit = $post['munit'];
        $waybillData->org = $post['org'];
        $waybillData->vat = $post['vat'];
        $waybillData->vat_included = $post['vat_included'];
        $sum = 0 + str_replace(',', '.', $post['sum']);
        $waybillData->sum = $sum;
        $quant = 0 + str_replace(',', '.', $post['quant']);
        $waybillData->quant = $quant;
        $waybillData->defsum = $post['defsum'];
        $waybillData->defquant = $post['defquant'];
        $koef = 0 + str_replace(',', '.', $post['koef']);
        $waybillData->koef = $koef;
        $waybillData->linked_at = $post['linked_at'];

        if (!$waybillData->validate() || !$waybillData->save()) {
            throw new ValidationException($waybillData->getFirstErrors());
        }

        return [
            "success"         => true,
            "waybill_data_id" => $waybillData->id
        ];
    }
}