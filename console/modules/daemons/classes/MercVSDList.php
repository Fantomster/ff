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
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\ListOptions;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\mercuryApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\VetDocumentsChangeList;
use yii\db\Expression;
use yii\helpers\BaseStringHelper;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercLogger;

/**
 * Class consumer with realization ConsumerInterface
 * and containing AbstractConsumer methods
 */
class MercVSDList extends MercDictConsumer
{
    const DEFAULT_STEP = 100;
    public static $timeout = 60 * 15;
    public static $timeoutExecuting = 60 * 60;
    private $result = true;

    public function init()
    {
        $check = 9;/*RabbitQueues::find()->where("consumer_class_name in ('MercUnitList', 'MercPurposeList',
        'MercCountryList', 'MercRussianEnterpriseList', 'MercForeignEnterpriseList', 'MercBusinessEntityList', 'MercProductList', 'MercProductItemList', 'MercSubProductList')")
            ->andWhere('start_executing is null and last_executed is not null and data_request is null')->count();*/

        if ($check == 9) {
            $this->queue = RabbitQueues::find()->where(['consumer_class_name' => 'MercVSDList', 'organization_id' => $this->org_id, 'store_id' => $this->data])->one();
            $this->data = json_decode($this->queue->data_request, true);
            if (!isset($this->data)) {
                $this->log('Not data for request' . PHP_EOL);
                throw new \Exception('Not data for request');
            }
        } else {
            $this->log('Dictionaries are currently being updated' . PHP_EOL);
            throw new \Exception('Dictionaries are currently being updated');
        }
    }

    public function getData()
    {
        $className = BaseStringHelper::basename(static::class);
        $this->init();
        $count = $this->data['listOptions']['offset'];
        $this->log('Load' . PHP_EOL);
        $error = 0;
        $list = null;
        $vsd = new VetDocumentsChangeList();
        $vsd->org_id = $this->org_id;
        $api = mercuryApi::getInstance($this->org_id);
        $api->setEnterpriseGuid($this->data['enterpriseGuid']);
        $curr_step = $this->data['listOptions']['count']; //Текущий шаг
        $curr_offset = $this->data['listOptions']['offset']; //Текущий отступ
        try {
            do {
                try {
                    //Записываем в базу данные о текущем шаге
                    $this->data['listOptions']['offset'] += $curr_step;
                    $curr_step = ($curr_step == 1) ? self::DEFAULT_STEP : $curr_step;
                    $this->data['listOptions']['count'] = $curr_step;
                    $this->data['listOptions'] = $this->data['listOptions'];
                    $this->queue->data_request = json_encode($this->data);
                    $this->queue->save();
                    //Выполняем запрос и обработку полученных данных
                    $result = $api->getVetDocumentChangeList($this->data['startDate'], $this->data['listOptions']);

                    //Проверяем результат запроса
                    if ($result->application->status != mercLog::COMPLETED) {
                        throw new \Exception(json_encode($result));
                    }

                    //Запрос успешный разбираем его
                    $vetDocumentList = $result->application->result->any['getVetDocumentChangesListResponse']->vetDocumentList;

                    $count += $vetDocumentList->count;
                    $this->log('Load ' . $count . ' / ' . $vetDocumentList->total . PHP_EOL);
                    echo 'Load ' . $count . ' / ' . $vetDocumentList->total . PHP_EOL;

                    //Готовимся к следующей итерации
                    $curr_step = self::DEFAULT_STEP;
                    $curr_offset += $curr_step;
                    $error = 0;
                } catch (\Throwable $e) {
                    $this->log($e->getMessage() . " " . $e->getTraceAsString() . PHP_EOL);
                    mercLogger::getInstance()->addMercLogDict('ERROR', BaseStringHelper::basename(static::class), $e->getMessage());
                    $error++;
                    if ($error == 3) {
                        if ($curr_step > 1) {
                            $curr_step = round($curr_step / 2);
                        }
                        else
                        {
                            $this->log('ERROR RECORD' . json_encode($this->request, true) . PHP_EOL);
                            $curr_step ++;
                            $error = 0;
                        }
                    }elseif ($error > 3) {
                        throw new \Exception('Error operation');
                    }
                }
                $total = $vetDocumentList->total ?? 0;
                $curr_count = $vetDocumentList->count ?? 0;
                $offset = $vetDocumentList->offset ?? $curr_offset;
                //sleep(60);
                echo $total.PHP_EOL;
                echo ($offset + $curr_count).PHP_EOL;
            } while ($total > ($offset + $curr_count));
        } catch (\Throwable $e) {
            $this->log($e->getMessage() . " " . $e->getTraceAsString() . PHP_EOL);
            mercLogger::getInstance()->addMercLogDict('ERROR', BaseStringHelper::basename(static::class), $e->getMessage());
            $this->addFCMMessage('MercVSDList', $this->data['enterpriseGuid']);
            throw new \Exception('Error operation');
        }
        $this->log("FIND: consumer_class_name = {$className}");

        MercVisits::updateLastVisit($this->org_id, MercVisits::LOAD_VSD_LIST, $this->data['enterpriseGuid']);

        $this->queue->data_request = new Expression('NULL');
        $this->queue->save();

        mercLogger::getInstance()->addMercLogDict('COMPLETE', BaseStringHelper::basename(static::class), null);

        $this->addFCMMessage('MercVSDList', $this->data['enterpriseGuid']);
    }

    public function saveData()
    {
        return $this->result;
    }
}