<?php
/**
 * Created by PhpStorm.
 * User: Silukov Konstantin
 * Date: 9/12/2018
 * Time: 3:24 PM
 */

namespace console\modules\daemons\classes;

use common\models\OuterProduct;
use common\models\OuterProductType;
use common\models\OuterUnit;
use console\modules\daemons\components\ConsumerInterface;
use console\modules\daemons\components\IikoSyncConsumer;

/**
 * Class IikoProductSync
 *
 * @package console\modules\daemons\classes
 */
class IikoProductSync extends IikoSyncConsumer implements ConsumerInterface
{
    /**@var array $items */
    private $items;

    /**
     * @var array
     */
    public $updates_uuid = [];

    /**
     * @var
     */
    public $success;

    /**
     * @var int
     */
    public static $timeout = 30;

    /**
     * @var int
     */
    public static $timeoutExecuting = 1200;

    /**
     * @var string
     */
    public $type = 'product';

    /**
     * Description
     *
     * @var array
     */
    public $types = [];

    /**
     * @throws \yii\web\BadRequestHttpException
     */
    public function getData()
    {
        $this->success = $this->run();
    }

    /**
     * @return mixed
     */
    public function saveData()
    {
        return $this->success['success'];
    }

    /**
     * Синхронизация продуктов
     *
     * @return int
     * @throws \Exception
     */
    protected function product()
    {
        $this->items = $this->iikoApi->getProducts();
        $this->iikoApi->logout();

        $this->types = OuterProductType::find()
            ->select(['id'])
            ->where(['service_id' => self::SERVICE_ID])
            ->indexBy('value')->asArray()->column();

        //Если пришли продукты, обновляем их
        if (!empty($this->items['products'])) {
            //поскольку мы не можем отследить изменения на стороне провайдера
            OuterProduct::updateAll(['is_deleted' => 1], ['org_id' => $this->orgId, 'service_id' => self::SERVICE_ID]);
            OuterUnit::updateAll(['is_deleted' => 1], ['org_id' => $this->orgId, 'service_id' => self::SERVICE_ID]);

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
                    ], ['outer_uid'  => $this->updates_uuid,
                        'service_id' => self::SERVICE_ID,
                    ])->execute();
            }
        }
        //Обновляем колличество полученных объектов
        return OuterProduct::find()->where(['is_deleted' => 0, 'org_id' => $this->orgId, 'service_id' => self::SERVICE_ID])->count();
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
        try {
            $model = OuterProduct::findOne(['outer_uid' => $uuid, 'org_id' => $this->orgId, 'service_id' => self::SERVICE_ID,]);
            $productType = $this->getOuterProductTypeID($item);
            //Если нет товара у нас, создаем
            if (empty($model)) {
                $model = new OuterProduct(['outer_uid' => $uuid]);
                $model->org_id = $this->orgId;
                $model->service_id = self::SERVICE_ID;
                $model->outer_product_type_id = $productType;
            }
            //Родительская категория если есть
            if (isset($item['parentId']) && !empty($item['parentId'])) {
                $model->parent_uid = $item['parentId'];
            }
            if (!empty($item['name'])) {
                $model->name = $item['name'];
            }

            if (is_null($model->outer_product_type_id) && !is_null($productType)) {
                $model->outer_product_type_id = $productType;
            }

            if (!empty($item['mainUnit'])) {
                $obUnitModel = OuterUnit::findOne([
                    'name'       => $item['mainUnit'],
                    'service_id' => self::SERVICE_ID,
                    'org_id'     => $this->orgId
                ]);

                if (!$obUnitModel) {
                    $obUnitModel = new OuterUnit();
                    $obUnitModel->name = $item['mainUnit'];
                    $obUnitModel->service_id = self::SERVICE_ID;
                    $obUnitModel->org_id = $this->orgId;
                    $obUnitModel->outer_uid = md5($item['mainUnit']);
                } else {
                    $obUnitModel->updated_at = \gmdate('Y-m-d H:i:s');
                }

                $obUnitModel->is_deleted = 0;
                $obUnitModel->save();

                $model->outer_unit_id = $obUnitModel->id;
            }

            //Валидируем сохраняем
            if ($model->attributes !== $model->oldAttributes) {
                $model->is_deleted = 0;
                $model->save(false);
            } else {
                $this->updates_uuid[] = $uuid;
            }
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param $item
     * @return int|mixed
     */
    private function getOuterProductTypeID($item)
    {
        return isset($this->types[$item['productType']]) ? $this->types[$item['productType']] : null;
    }
}
