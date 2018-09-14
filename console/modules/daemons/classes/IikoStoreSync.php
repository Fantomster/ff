<?php
/**
 * Created by PhpStorm.
 * User: Fanto
 * Date: 9/14/2018
 * Time: 1:26 PM
 */

namespace console\modules\daemons\classes;


use api\common\models\iiko\iikoDictype;
use api_web\exceptions\ValidationException;
use common\models\OuterStore;
use console\modules\daemons\components\IikoSyncConsumer;
use console\modules\daemons\components\ConsumerInterface;
use frontend\modules\clientintegr\modules\iiko\helpers\iikoApi;
use yii\web\BadRequestHttpException;

class IikoStoreSync extends IikoSyncConsumer implements ConsumerInterface
{
    public $updates_uuid = [];

    public $success;

    public static $timeout = 600;

    public static $timeoutExecuting = 300;

    public function getData()
    {
        $model = iikoDictype::findOne(['method' => 'store']);
        $this->success = $this->run($model->id);
    }

    public function saveData()
    {
        return $this->success['success'];
    }

    /**
     * Синхронизация складов
     * @return integer
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    protected function store()
    {
        //Получаем список складов
        $stores = iikoApi::getInstance($this->orgId)->getStores();
        /**/
        array_unshift($stores['corporateItemDto'], ['id' => md5($this->orgId), 'name' => 'Все склады', 'type' => 'rootnode']);
        if (!empty($stores['corporateItemDto'])) {
            //поскольку мы не можем отследить изменения на стороне провайдера
            OuterStore::updateAll(['is_deleted' => 1], ['org_id' => $this->orgId, 'service_id' => self::SERVICE_ID]);
            foreach ($stores['corporateItemDto'] as $store) {
                $model = OuterStore::findOne(['outer_uid' => $store['id'], 'org_id' => $this->orgId, 'service_id' => self::SERVICE_ID]);
                //Если нет категории у нас, создаем
                if (empty($model)) {
                    $model = new OuterStore([
                        'outer_uid' => $store['id'],
                        'org_id' => $this->orgId,
                        'service_id' => self::SERVICE_ID,
                    ]);
                }
                $model->is_deleted = 0;
                if (!empty($store['name'])) {
                    $model->name = $store['name'];
                }
                if (!empty($store['type'])) {
                    if($store['type'] == 'rootnode'){
                        $model->makeRoot();
                        $rootNode = $model;
                    } else {
                        $model->prependTo($rootNode);
                        $model->store_type = $store['type'];
                    }
                }

                //Валидируем сохраняем
                if (!$model->validate() || !$model->save()) {
                    $this->log($model->getErrors());
                    throw new ValidationException($model->getFirstErrors());
                }
            }
        }
        //Обновляем колличество полученных объектов
        return (int)OuterStore::find()->where(['is_deleted' => 0, 'org_id' => $this->orgId, 'service_id' => self::SERVICE_ID])->count();
    }

}