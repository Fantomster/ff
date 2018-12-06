<?php

namespace api_web\classes;

use Yii;
use api_web\components\Registry;
use api_web\modules\integration\classes\sync\RkwsAgent;
use api_web\modules\integration\classes\sync\RkwsCategory;
use api_web\modules\integration\classes\sync\RkwsUnit;
use api_web\modules\integration\classes\sync\RkwsProduct;
use api_web\modules\integration\classes\sync\RkwsStore;
use api_web\modules\integration\classes\sync\RkwsWaybill;
use api_web\modules\integration\classes\sync\ServiceRkws;
use common\models\Journal;
use common\models\Waybill;
use api_web\modules\integration\classes\sync\AbstractSyncFactory;
use api_web\modules\integration\classes\SyncServiceFactory;
use yii\web\BadRequestHttpException;
use common\models\AllServiceOperation;
use common\models\OuterTask;

class NoAuthWebApi
{

    /**
     * Код операции => Класс который будет ее обрабатывать
     *
     * @return array
     */
    public static function getAllSyncOperations(): array
    {
        return [
            32 => RkwsAgent::class,
            23 => RkwsCategory::class,
            34 => RkwsUnit::class,
            25 => RkwsStore::class,
            24 => RkwsProduct::class,
            33 => RkwsWaybill::class
        ];
    }

    /**
     * Загрузка справочников R-keeper
     *
     * @param OuterTask $task
     * @return string
     * @throws BadRequestHttpException
     */
    public function loadDictionary(OuterTask $task)
    {
        $operation = AllServiceOperation::findOne(['code' => $task->oper_code, 'service_id' => $task->service_id]);
        if (!$operation) {
            throw new BadRequestHttpException(\Yii::t('api_web', "wrong_param{param}", ['ru'=>'Некорректный параметр|{param}', 'param' =>  AbstractSyncFactory::CALLBACK_TASK_IDENTIFIER]));
        }

        $allOperations = self::getAllSyncOperations();
        if (isset($allOperations[$operation->code])) {
            $class  = $allOperations[$operation->code];
            $entity = new $class(SyncServiceFactory::ALL_SERVICE_MAP[$operation->service_id], $operation->service_id);
            $entity->log('`task_id` : ' . $task->id);
            if ($entity instanceof ServiceRkws) {
                $entity->orgId = $task->org_id;
            }
            /** @var $entity AbstractSyncFactory */
            if (method_exists($entity, 'parsingXml')) {
                $body = Yii::$app->request->getRawBody();
                if (empty($body)) {
                    $entity->log('Тело ответа пустое: ' . $task->id);
                    return 'false';
                }
                $entity->log($body);
                $res = $entity->callbackData($task, $body);
                $entity->log($res);
                return $res;
            }
        }
        \Yii::error('Не найден класс для обработки операции: ' . $operation->code);
        return 'false';
    }

    /**
     * Отправка накладной R-keeper
     *
     * @param OuterTask $task
     * @return string
     * @throws BadRequestHttpException
     */
    public function sendWaybill(OuterTask $task)
    {
        $operation = AllServiceOperation::findOne(['code' => $task->oper_code, 'service_id' => $task->service_id]);
        if (!$operation) {
            throw new BadRequestHttpException(\Yii::t('api_web', "wrong_param{param}", ['ru'=>'Некорректный параметр|{param}', 'param' =>  AbstractSyncFactory::CALLBACK_TASK_IDENTIFIER]));
        }

        $allOperations = self::getAllSyncOperations();
        if (isset($allOperations[$operation->code])) {
            $entityName = $allOperations[$operation->code];
            $entity     = new $entityName(SyncServiceFactory::ALL_SERVICE_MAP[$operation->service_id], $operation->service_id);
            $entity->log('Callback operation `task_id` params is ' . $task->id);
            /** @var RkwsWaybill $entity */
            if (method_exists($entity, 'receiveXMLDataWaybill')) {
                $body                     = Yii::$app->request->getRawBody();
                $res                      = $entity->receiveXMLDataWaybill($task, $body);
                $xml                      = (array) simplexml_load_string($body);
                $waybill                  = Waybill::findOne($task->waybill_id);
                $journal                  = new Journal();
                $journal->service_id      = $waybill->service_id;
                $journal->operation_code  = $operation->code;
                $journal->log_guide       = 'any_call';
                $journal->organization_id = $waybill->acquirer_id;
                // Когда все хорошо и накладная создалась в R-keeper
                if (array_key_exists('DOC', $xml)) {
                    $doc                        = (array) $xml['DOC'];
                    $waybill->status_id         = Registry::WAYBILL_UNLOADED;
                    $waybill->outer_document_id = $doc['@attributes']['rid'];
                    $journal->type              = 'success';
                    $journal->response          = "waybill.id = " . $waybill->id;
                } elseif (array_key_exists('ERROR', $xml)) {
                    //Когда случилась ошибка
                    $error              = (array) $xml['ERROR'];
                    $waybill->status_id = Registry::WAYBILL_ERROR;
                    $journal->type      = 'error';
                    $journal->response  = $error['@attributes']['Text'];
                }
                $waybill->save();
                $journal->save();
                $entity->log($res);
                return $res;
            }
            $entity->log('Fail!');
        }
        return 'false';
    }

}
