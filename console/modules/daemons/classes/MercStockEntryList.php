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

    public function init()
    {
        $check = 9;/*RabbitQueues::find()->where("consumer_class_name in ('MercUnitList', 'MercPurposeList',
        'MercCountryList', 'MercRussianEnterpriseList', 'MercForeignEnterpriseList', 'MercBusinessEntityList', 'MercProductList', 'MercProductItemList', 'MercSubProductList')")
            ->andWhere('start_executing is null and last_executed is not null and data_request is null')->count();*/

        if ($check == 9) {
            $this->queue = RabbitQueues::find()->where(['consumer_class_name' => 'MercStockEntryList', 'organization_id' => $this->org_id, 'store_id' => $this->data])->one();
            $this->data = json_decode(($this->queue->data_request ?? $this->data), true);
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
        $vsd = new LoadStockEntryList();
        $vsd->org_id = $this->org_id;
        $api = mercuryApi::getInstance($this->org_id);
        $api->setEnterpriseGuid($this->data['enterpriseGuid']);
        $step = $this->data['listOptions']['count'];
        try {
            do {
                try {
                    //Записываем в базу данные о текущем шаге
                    $this->data['listOptions'] = $this->data['listOptions'];
                    $this->queue->data_request = json_encode($this->data);
                    $this->queue->save();
                    //Выполняем запрос и обработку полученных данных
                    $result = $api->getStockEntryChangesList($this->data['startDate'], $this->data['listOptions']);
                    if ($result->application->status == mercLog::REJECTED) {
                        sleep(5);
                        continue;
                    }
                    $stockEntryList = $result->application->result->any['getStockEntryChangesListResponse']->stockEntryList;
                    $count += $stockEntryList->count;
                    $this->log('Load ' . $count . ' / ' . $stockEntryList->total . PHP_EOL);

                    if ($stockEntryList->count > 0) {
                        $vsd->updateDocumentsList($stockEntryList->stockEntry);
                    }

                    if ($stockEntryList->count < $stockEntryList->total) {
                        $this->data['listOptions']['offset'] += $step;
                        $step = self::DEFAULT_STEP;
                        $this->data['listOptions']['count'] = $step;
                    }

                    $error = 0;
                } catch (\Throwable $e) {
                    $this->log($e->getMessage() . " " . $e->getTraceAsString() . PHP_EOL);
                    mercLogger::getInstance()->addMercLogDict('ERROR', BaseStringHelper::basename(static::class), $e->getMessage());
                    $error++;
                    if ($error == 3) {
                        //throw new \Exception('Error operation');
                        if ($step > 1) {
                            $step = round($step / 2);
                            $this->data['listOptions']['count'] = $step;
                            //$this->request['listOptions']['offset'] += $this->request['listOptions']['count'];
                        }
                        else
                        {
                            $this->log('ERROR RECORD' . json_encode($this->request, true) . PHP_EOL);
                            $step = self::DEFAULT_STEP;
                            $this->data['listOptions']['count'] = $step;
                            $this->data['listOptions']['offset'] += 1;
                            $error = 0;
                        }
                    }elseif ($error > 3) {
                        throw new \Exception('Error operation');
                    }
                }
                $total = $stockEntryList->total ?? ($this->data['listOptions']['count'] + $this->data['listOptions']['offset'] +1);
                sleep(60);
            } while ($total > ($this->data['listOptions']['count'] + $this->data['listOptions']['offset']));
        } catch (\Throwable $e) {
            $this->log($e->getMessage() . " " . $e->getTraceAsString() . PHP_EOL);
            mercLogger::getInstance()->addMercLogDict('ERROR', BaseStringHelper::basename(static::class), $e->getMessage());
            $this->addFCMMessage('MercStockEntryList', $this->data['enterpriseGuid']);
            throw new \Exception('Error operation');
        }

        $this->log("FIND: consumer_class_name = {$className}");

        MercVisits::updateLastVisit($this->org_id, MercVisits::LOAD_STOCK_ENTRY_LIST, $this->data['enterpriseGuid']);

        $this->queue->data_request = new Expression('NULL');
        $this->queue->save();

        mercLogger::getInstance()->addMercLogDict('COMPLETE', BaseStringHelper::basename(static::class), null);

        $this->addFCMMessage('MercStockEntryList', $this->data['enterpriseGuid']);
    }

    public function saveData()
    {
        return $this->result;
    }
}