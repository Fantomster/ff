<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 26.08.2018
 * Time: 17:24
 */

namespace console\modules\daemons\components;

use api\common\models\merc\mercPconst;
use api\common\models\RabbitQueues;
use frontend\modules\clientintegr\modules\merc\helpers\api\baseApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercLogger;
use yii\db\Expression;
use yii\helpers\BaseStringHelper;
use api_web\components\FireBase;

/**
 * Class consumer with realization ConsumerInterface
 * and containing AbstractConsumer methods
 */
class MercDictConsumer extends AbstractConsumer implements ConsumerInterface
{
    const DEFAULT_STEP = 1000;

    /**
     * Description
     * @var baseApi
     */
    protected $instance;
    protected $method;
    protected $startDate;
    protected $listName;
    protected $listItemName;
    protected $request;
    protected $org_id;
    protected $modelClassName;
    protected $queue;

    public function __construct($org_id = null)
    {
        if ($org_id != null) {
            $this->org_id = $org_id;
        } else {
            $this->org_id = 0;
        }
        $this->logPrefix = $org_id;
    }

    /**
     * Обработка и сохранение результата
     * @param $list
     */
    protected function saveList($list)
    {
        $list = is_array($list) ? $list : [$list];
        $result = [];
        foreach ($list as $item) {
            $model = $this->modelClassName::findOne(['uuid' => $item->uuid]);

            if ($model == null) {
                $model = new $this->modelClassName();
            }
            $attributes = json_decode(json_encode($item), true);
            $model->setAttributes($attributes, false);
            $model->active = (int)$attributes['active'];
            $model->last = (int)$attributes['last'];
            $model->data = serialize($item);
            $model->createDate = date('Y-m-d H:i:s', strtotime($model->createDate));
            $model->updateDate = date('Y-m-d H:i:s', strtotime($model->updateDate));
            if (!$model->save()) {
                $result[]['error'] = $model->getErrors();
                $result[]['model-data'] = $model->attributes;
            }
        }

        return $result;
    }

    /**
     * инициализация свойств класса
     */
    protected function init()
    {
        $this->queue = RabbitQueues::find()->where(['consumer_class_name' => BaseStringHelper::basename(static::class)])->one();
        $this->data = json_decode($this->queue->data_request ?? $this->data, true);
        $this->method = $this->data['method'];
        $this->request = json_decode($this->data['request'], true);
        $this->listName = $this->data['struct']['listName'];
        $this->listItemName = $this->data['struct']['listItemName'];
    }

    /**
     * @throws \Exception
     */
    public function getData()
    {
        $this->init();
        $this->log('Load' . PHP_EOL);
        $error = 0;
        $list = null;
        $offset = $this->request['listOptions']['offset'];
        $step = $this->request['listOptions']['count'];
        try {
            do {
                try {
                    //Записываем в базу данные о текущем шаге
                    $this->data['request'] = json_encode($this->request);
                    if(isset($this->queue)) {
                        $this->queue->data_request = json_encode($this->data);
                        $this->queue->save();
                    }

                    //Выполняем запрос и обработку полученных данных
                    $response = $this->instance->sendRequest($this->method, $this->request);
                    $list = $response->{$this->listName};
                    $offset += $list->count;
                    $this->log('Load ' . $offset. ' / ' . $list->total . PHP_EOL);
                    echo 'Load ' . $offset. ' / ' . $list->total . PHP_EOL;

                    if ($list->count > 0) {
                        $result = $this->saveList($list->{$this->listItemName});
                        if (!empty($result)) {
                            $this->log('ERROR ' . json_encode($result, true) . PHP_EOL);
                        }
                    }

                    if ($list->count < $list->total) {
                        $this->request['listOptions']['offset'] += $step;
                        $step = self::DEFAULT_STEP;
                        $this->request['listOptions']['count'] = $step;
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
                            $this->request['listOptions']['count'] = $step;
                            //$this->request['listOptions']['offset'] += $this->request['listOptions']['count'];
                        }
                        else
                        {
                            $this->log('ERROR RECORD' . json_encode($this->request, true) . PHP_EOL);
                            $step = self::DEFAULT_STEP;
                            $this->request['listOptions']['count'] = $step;
                            $this->request['listOptions']['offset'] += 1;
                            $error = 0;
                        }
                    }elseif ($error > 3) {
                        throw new \Exception('Error operation');
                    }
                }
              $total = $list->total ?? ($this->request['listOptions']['count'] + $this->request['listOptions']['offset'] +1);
            } while ($total > ($this->request['listOptions']['count'] + $this->request['listOptions']['offset']));
        } catch (\Throwable $e) {
            $this->log($e->getMessage() . " " . $e->getTraceAsString() . PHP_EOL);
            mercLogger::getInstance()->addMercLogDict('ERROR', BaseStringHelper::basename(static::class), $e->getMessage());
            throw new \Exception('Error operation');
        }

        $this->log("Complete operation success");

        if(isset($this->queue)) {
            $this->queue->data_request = new Expression('NULL');
            $this->queue->save();
        }

        mercLogger::getInstance()->addMercLogDict('COMPLETE', BaseStringHelper::basename(static::class), null);
    }

    /**
     * @return mixed
     */
    public function saveData()
    {
        return true;
    }

    public function addFCMMessage($operation, $org_id)
    {
        FireBase::getInstance()->update([
            'mercury',
            'operation' => $operation."_".$org_id,
        ], [
            'last_executed' => gmdate("Y-m-d H:i:s"),
            'plain_executed' => gmdate("Y-m-d H:i:s", time() + 15*60)
        ]);
    }
}