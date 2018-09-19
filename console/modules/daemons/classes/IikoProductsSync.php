<?php
/**
 * Created by PhpStorm.
 * User: Fanto
 * Date: 9/12/2018
 * Time: 3:24 PM
 */

namespace console\modules\daemons\classes;

use api\common\models\iiko\iikoDictype;
use api_web\exceptions\ValidationException;
use common\models\OuterProduct;
use common\models\OuterUnit;
use console\modules\daemons\components\ConsumerInterface;
use console\modules\daemons\components\IikoSyncConsumer;
use frontend\modules\clientintegr\modules\iiko\helpers\iikoApi;

class IikoProductsSync extends IikoSyncConsumer implements ConsumerInterface
{
    /**@var $items array */
    private $items;

    public $updates_uuid = [];

    public $success;

    public static $timeout = 600;

    public static $timeoutExecuting = 300;

    public function getData()
    {
        $model = iikoDictype::findOne(['method' => 'goods']);
        $this->success = $this->run($model->id);
    }

    public function saveData()
    {
        return $this->success['success'];
    }

    /**
     * Синхронизация продуктов
     * @return int
     * @throws ValidationException
     */
    protected function goods()
    {
        $this->items = iikoApi::getInstance($this->orgId)->getProducts();

        //Если пришли продукты, обновляем их
        if (!empty($this->items['products'])) {
            //поскольку мы не можем отследить изменения на стороне провайдера
            OuterProduct::updateAll(['is_deleted' => 1], ['org_id' => $this->orgId, 'service_id' => self::SERVICE_ID]);

            $generator = function ($items) {
                foreach ($items as &$item) {
                    yield $item;
                }
            };

            foreach ($generator($this->items['products']) as $item) {
                $this->updateProduct($item['id'], $item);
            }

            if (!empty($this->updates_uuid)) {
                \Yii::$app->db_api->createCommand()
                    ->update(OuterProduct::tableName(), [
                        'is_deleted' => 0,
                        'updated_at' => \gmdate('Y-m-d H:i:s')
                    ], ['outer_uid' => $this->updates_uuid,
                        'service_id' => self::SERVICE_ID,
                        ])
                    ->execute();
            }
        }
        //Обновляем колличество полученных объектов
        return OuterProduct::find()->where(['is_deleted' => 0, 'org_id' => $this->orgId, 'service_id' => self::SERVICE_ID])->count();
    }


    /**
     * Обновление продукта
     * @param $uuid
     * @param $item
     * @return bool
     * @throws \Exception
     */
    private function updateProduct($uuid, $item)
    {
        $transaction = \Yii::$app->get('db_api')->beginTransaction();
        try {
            $model = OuterProduct::findOne(['outer_uid' => $uuid, 'org_id' => $this->orgId, 'service_id' => self::SERVICE_ID,]);
            //Если нет товара у нас, создаем
            if (empty($model)) {
                $model = new OuterProduct(['outer_uid' => $uuid]);
                $model->org_id = $this->orgId;
                $model->service_id = self::SERVICE_ID;
            }
            //Родительская категория если есть
            if (isset($item['parentId']) && !empty($item['parentId'])) {
                $model->parent_uid = $item['parentId'];
            }
            if (!empty($item['name'])) {
                $model->name = $item['name'];
            }

            if (!empty($item['mainUnit'])) {
                $obUnitModel = OuterUnit::findOne(['name' => $item['mainUnit'], 'service_id' => self::SERVICE_ID]);
                if (!$obUnitModel) {
                    $obUnitModel = new OuterUnit();
                    $obUnitModel->name = $item['mainUnit'];
                    $obUnitModel->service_id = self::SERVICE_ID;
                    if ($obUnitModel->validate()) {
                        $obUnitModel->save();
                    }
                }
                $model->outer_unit_id = $obUnitModel->id;
            }

            //Валидируем сохраняем
            if ($model->attributes !== $model->oldAttributes) {
                $model->is_deleted = 0;
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
}