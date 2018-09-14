<?php
/**
 * Created by PhpStorm.
 * User: Fanto
 * Date: 9/14/2018
 * Time: 11:01 AM
 */

namespace console\modules\daemons\classes;


use api\common\models\iiko\iikoDictype;
use api_web\exceptions\ValidationException;
use common\models\OuterAgent;
use console\modules\daemons\components\IikoSyncConsumer;
use console\modules\daemons\components\ConsumerInterface;
use frontend\modules\clientintegr\modules\iiko\helpers\iikoApi;

class IikoAgentSync extends IikoSyncConsumer implements ConsumerInterface
{
    /**@var $items array */
    private $items;

    public $updates_uuid = [];

    public $success;

    public static $timeout = 600;

    public static $timeoutExecuting = 300;

    public function getData()
    {
        $model = iikoDictype::findOne(['method' => 'agent']);
        $this->success = $this->run($model->id);
    }

    public function saveData()
    {
        return $this->success['success'];
    }

    /**
     * Синхронизация контрагентов
     * @return int
     * @throws ValidationException
     */
    protected function agent()
    {
        $agents = iikoApi::getInstance($this->orgId)->getSuppliers();
        if (!empty($agents['employee'])) {
            //поскольку мы не можем отследить изменения на стороне провайдера
            OuterAgent::updateAll(['is_deleted' => 1], ['org_id' => $this->orgId, 'service_id' => self::SERVICE_ID]);
            foreach ($agents['employee'] as $agent) {
                $model = OuterAgent::findOne(['outer_uid' => $agent['id'], 'org_id' => $this->orgId, 'service_id' => self::SERVICE_ID]);
                //Если нет у нас, создаем
                if (empty($model)) {
                    $model = new outerAgent(['outer_uid' => $agent['id']]);
                    $model->org_id = $this->orgId;
                    $model->service_id = self::SERVICE_ID;
                }
                $model->is_deleted = 0;
                $model->name = $agent['name'];
                //Валидируем сохраняем
                if (!$model->validate() || !$model->save()) {
                    throw new ValidationException($model->getErrorSummary(true));
                }
            }
        }
        //Обновляем колличество полученных объектов
        return OuterAgent::find()->where(['is_deleted' => 0, 'org_id' => $this->orgId])->count();
    }


}