<?php
/**
 * Created by PhpStorm.
 * Developer: Arsen
 * Vk: https://vk.com/a.arsik
 * Inst: https://www.instagram.com/arsen.web/
 * Date: 2019-01-11
 * Time: 11:17
 */

namespace console\modules\daemons\classes;

use api_web\exceptions\ValidationException;
use common\models\OuterAgent;
use console\modules\daemons\components\ConsumerInterface;
use console\modules\daemons\components\TillypadSyncConsumer;

class TillypadAgentSync extends TillypadSyncConsumer implements ConsumerInterface
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
    public static $timeoutExecuting = 600;

    /**
     * @var string
     */
    public $type = 'agent';

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
     * Синхронизация контрагентов
     *
     * @return int
     * @throws ValidationException|\Exception
     */
    protected function agent()
    {
        $agents = $this->tillypadApi->getSuppliers();
        $this->tillypadApi->logout();

        if (!empty($agents['employee'])) {
            //поскольку мы не можем отследить изменения на стороне провайдера
            OuterAgent::updateAll(['is_deleted' => 1], ['org_id' => $this->orgId, 'service_id' => self::SERVICE_ID]);
            foreach ($agents['employee'] as $agent) {
                $model = OuterAgent::findOne(['outer_uid' => $agent['id'], 'org_id' => $this->orgId, 'service_id' => self::SERVICE_ID]);
                //Если нет у нас, создаем
                if (empty($model)) {
                    $model = new OuterAgent(['outer_uid' => $agent['id']]);
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
        return OuterAgent::find()->where(['is_deleted' => 0, 'org_id' => $this->orgId, 'service_id' => self::SERVICE_ID])->count();
    }
}