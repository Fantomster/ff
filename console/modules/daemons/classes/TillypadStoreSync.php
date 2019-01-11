<?php

namespace console\modules\daemons\classes;

use api_web\exceptions\ValidationException;
use common\models\OuterStore;
use console\modules\daemons\components\ConsumerInterface;
use console\modules\daemons\components\TillypadSyncConsumer;

class TillypadStoreSync extends TillypadSyncConsumer implements ConsumerInterface
{
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
    public static $timeoutExecuting = 2400;

    /**
     * @var string
     */
    public $type = 'store';

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
     * Синхронизация складов
     *
     * @return integer
     * @throws \Exception
     */
    protected function store()
    {
        //Получаем список складов
        $stores = $this->tillypadApi->getStores();
        $this->tillypadApi->logout();
        /** Вставляем корневой склад для tillypad потому что там таких нет*/
        array_unshift($stores['corporateItemDto'], ['id' => md5($this->orgId), 'name' => 'Все склады', 'type' => 'rootnode']);
        if (!empty($stores['corporateItemDto'])) {
            //поскольку мы не можем отследить изменения на стороне провайдера
            OuterStore::updateAll(['is_deleted' => 1], ['org_id' => $this->orgId, 'service_id' => self::SERVICE_ID]);
            foreach ($stores['corporateItemDto'] as $store) {
                $model = OuterStore::findOne(['outer_uid' => $store['id'], 'org_id' => $this->orgId, 'service_id' => self::SERVICE_ID]);
                //Если нет категории у нас, создаем
                if (empty($model)) {
                    $model = new OuterStore([
                        'outer_uid'  => $store['id'],
                        'org_id'     => $this->orgId,
                        'service_id' => self::SERVICE_ID,
                    ]);

                    if (!empty($store['type'])) {
                        if ($store['type'] == 'rootnode') {

                            $model->makeRoot();
                            $rootNode = $model;
                        } else {
                            /** @var OuterStore $rootNode */
                            $model->prependTo($rootNode);
                            $model->store_type = $store['type'];
                        }
                    }
                }
                $model->is_deleted = 0;

                if (!empty($store['name'])) {
                    $model->name = $store['name'];
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