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

/**
 * Class consumer with realization ConsumerInterface
 * and containing AbstractConsumer methods
 */
class MercDictConsumer extends AbstractConsumer implements ConsumerInterface
{
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
        $count = $this->request['listOptions']['offset'];
        $this->log('Load' . PHP_EOL);
        $error = 0;
        $list = null;
        do {
            try {
                //Записываем в базу данные о текущем шаге
                $this->data['request'] = json_encode($this->request);
                $this->queue->data_request = json_encode($this->data);
                $this->queue->save();

                //Выполняем запрос и обработку полученных данных
                $response = $this->instance->sendRequest($this->method, $this->request);
                $list = $response->{$this->listName};
                $count += $list->count;
                $this->log('Load ' . $count . ' / ' . $list->total . PHP_EOL);
                echo 'Load ' . $count . ' / ' . $list->total . PHP_EOL;

                if ($list->count > 0) {
                    $result = $this->saveList($list->{$this->listItemName});
                    if (!empty($result)) {
                        $this->log('ERROR ' . json_encode($result, true) . PHP_EOL);
                    }
                }

                if ($list->count < $list->total) {
                    $this->request['listOptions']['offset'] += $list->count;
                }
            } catch (\Throwable $e) {
                $this->log($e->getMessage() . " " . $e->getTraceAsString() . PHP_EOL);
                mercLogger::getInstance()->addMercLogDict('ERROR', BaseStringHelper::basename(static::class), $e->getMessage());
                $error++;
                if ($error == 3) {
                    throw new \Exception('Error operation');
                }
            }
        } while (isset($list->total, $list->count, $list->offset) && ($list->total > ($list->count + $list->offset)));

        $this->log("FIND: consumer_class_name = ".BaseStringHelper::basename(static::class));

        $this->queue->data_request = new Expression('NULL');
        $this->queue->save();

        mercLogger::getInstance()->addMercLogDict('COMPLETE', BaseStringHelper::basename(static::class), null);
    }

    /**
     * @return mixed
     */
    public function saveData()
    {
        return true;
    }
}