<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 26.08.2018
 * Time: 17:24
 */

namespace console\modules\daemons\classes;

use api\common\models\merc\mercLog;
use api\common\models\merc\MercVisits;
use api\common\models\RabbitQueues;
use console\modules\daemons\components\MercDictConsumer;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\LoadStockEntryList;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\mercuryApi;
use yii\db\Expression;
use yii\helpers\BaseStringHelper;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercLogger;

/**
 * Class consumer with realization ConsumerInterface
 * and containing AbstractConsumer methods
 */
class MercStockEntryList extends MercDictConsumer
{
    const DEFAULT_STEP = 100;
    public static $timeout = 60 * 15;
    public static $timeoutExecuting = 60 * 60;
    private $result = true;
    private $queue_job_uid = null;

    public function init()
    {
        $this->addFCMMessage('MercStockEntryList', $this->org_id);
        $this->data = json_decode($this->data, true);
        $this->queue_job_uid = isset($this->data['job_uid']) ? $this->data['job_uid'] : null;
        $this->queue = RabbitQueues::find()->where(['consumer_class_name' => 'MercStockEntryList', 'organization_id' => $this->org_id, 'store_id' => $this->data['enterpriseGuid']])->one();
        $this->data = isset($this->queue->data_request) ? json_decode($this->queue->data_request, true) : $this->data;
        if (!isset($this->data)) {
            $this->log('Not data for request' . PHP_EOL);
            throw new \Exception('Not data for request');
        }
    }

    public function getData()
    {
        try {
            $className = BaseStringHelper::basename(static::class);
            $this->init();
            $this->log('Load' . PHP_EOL);
            $count_error = 0;
            $list = null;
            $vsd = new LoadStockEntryList();
            $vsd->org_id = $this->org_id;
            $api = mercuryApi::getInstance($this->org_id);
            $api->setEnterpriseGuid($this->data['enterpriseGuid']);
            $curr_step = $this->data['listOptions']['count']; //Текущий шаг
            $curr_offset = $this->data['listOptions']['offset']; //Текущий отступ
            $total = 0;
            $add_curr_offset = 0;
            do {
                try {
                    //Записываем в базу данные о текущем шаге
                    $add_curr_offset = 0;
                    $this->data['listOptions']['offset'] = $curr_offset;
                    $this->data['listOptions']['count'] = $curr_step;
                    $this->data['listOptions'] = $this->data['listOptions'];

                    if(isset($this->queue)) {
                        $this->queue->data_request = json_encode($this->data);
                        $this->queue->save();
                    }
                    echo "============================" . PHP_EOL;
                    //Выполняем запрос и обработку полученных данных
                    $load_data_succ = false;
                    $result = $api->getStockEntryChangesList($this->data['startDate'], $this->data['listOptions']);

                    //Проверяем результат запроса
                    if ($result->application->status != mercLog::COMPLETED) {
                        throw new \Exception($result->application->status);
                    }

                    $load_data_succ = true;

                    //Запрос успешный разбираем его
                    $stockEntryList = $result->application->result->any['getStockEntryChangesListResponse']->stockEntryList;
                    $count = $curr_offset + $stockEntryList->count;
                    $this->log('Load ' . $count . ' / ' . $stockEntryList->total . PHP_EOL);
                    echo 'Load ' . $count . ' / ' . $stockEntryList->total . PHP_EOL;

                    if ($stockEntryList->count > 0) {
                        $vsd->updateDocumentsList($stockEntryList->stockEntry);
                    } elseif ($stockEntryList->total == 0) {
                        break;
                    }

                    //Готовимся к следующей итерации
                    $curr_count = $stockEntryList->count ?? 0;
                    $curr_step = self::DEFAULT_STEP;
                    $curr_offset = $stockEntryList->offset;
                    $count_error = 0;
                } catch (\Throwable $e) {
                    $this->log($e->getMessage() . " " . $e->getTraceAsString() . PHP_EOL);
                    mercLogger::getInstance()->addMercLogDict('ERROR', BaseStringHelper::basename(static::class), $e->getMessage(), $this->org_id);
                    If (isset($result->application->errors)) {
                        if ($result->application->errors->error->code == 'APLM0012') {
                            echo "Error APLM0012" . PHP_EOL;
                            throw new \Exception('Error APLM0012');
                        }
                    }
                    echo "Error " . PHP_EOL;
                    $curr_count = 0; //Если произошла ошибка значит данные мы на этой итерации не получили
                    if ($count_error >= 3) {
                        if ($load_data_succ) {
                            echo "Error 0" . PHP_EOL;
                            //Если ошибка повторилась 3 раза и шаг более 1, уменьшаем шаг на половину
                            if ($curr_step > 1) {
                                $curr_step = round($curr_step / 2);
                            } else {
                                //Если ошибка повторилась 3 раза и шаг равен 1, записываем данные о битом запросе в лог и пропускаем данную запись
                                echo "Error 00" . PHP_EOL;
                                $this->log('ERROR RECORD' . json_encode($this->request, true) . PHP_EOL);
                                $add_curr_offset++;
                            }
                            $count_error = 0; //Даем еще три попытки
                            $load_data_succ = true;
                        } else {
                            echo "Error 1 " . PHP_EOL;
                            $load_data_succ = false;
                        }
                    } else {
                        $load_data_succ = true;
                    }
                    $count_error++;
                }
                $total = $stockEntryList->total ?? $total;
                $offset = $curr_offset;
                $curr_offset += $curr_count;
                $curr_offset += $add_curr_offset;
                //sleep(60);
                echo "total " . $total . PHP_EOL;
                echo "curr_count " . $curr_count . PHP_EOL;
                echo "curr_offset " . $curr_offset . PHP_EOL;
                echo "offset " . $offset . PHP_EOL;
                echo "error " . $count_error . PHP_EOL;
                echo "curr step " . $curr_step . PHP_EOL;
                echo "load_data_succ ";
                var_dump($load_data_succ);
                var_dump(!($total >= $curr_offset));

                //Вычисляем условие завершения цикла
                $condition = $load_data_succ;
                if ($total > 0) {
                    echo "Cond" . PHP_EOL;
                    if ($condition)
                        $condition = ($total > $curr_offset);
                    else
                        $condition || !($total >= $curr_offset);
                }
                echo "Cond " . var_dump($condition) . PHP_EOL;
                echo "============================" . PHP_EOL;
                sleep(60);
            } while ($condition);
            if ($count_error > 0) {
                throw new \Exception('Cancel error operation');
            }
        } catch (\Throwable $e) {
            $this->log($e->getMessage() . " " . $e->getTraceAsString() . PHP_EOL);
            mercLogger::getInstance()->addMercLogDict('ERROR', BaseStringHelper::basename(static::class), $e->getMessage(), $this->org_id);
            throw new \Exception('Error operation');
        }

        $this->log("FIND: consumer_class_name = {$className}");

        MercVisits::updateLastVisit($this->org_id, MercVisits::LOAD_STOCK_ENTRY_LIST, $this->data['enterpriseGuid']);
        mercLogger::getInstance()->addMercLogDict('COMPLETE', BaseStringHelper::basename(static::class), null, $this->org_id);

        if (isset($this->queue)) {
            $this->queue->data_request = new Expression('NULL');
            $this->queue->save();
        }

        $curr_job_uid = isset($this->data['job_uid']) ? $this->data['job_uid'] : null;
        if($this->queue_job_uid != $curr_job_uid) {
            $this->result = false;
        }
    }

    public function saveData()
    {
        return $this->result;
    }
}