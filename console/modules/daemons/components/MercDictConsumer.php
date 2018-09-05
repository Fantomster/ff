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

    public function __construct($org_id = null)
    {
        if ($org_id != null) {
            $this->org_id = $org_id;
        } else {
            $this->org_id = (mercPconst::findOne('1'))->org;
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
    }

    /**
     * @throws \Exception
     */
    public function getData()
    {
        $className = BaseStringHelper::basename(get_class(self::class));
        $queue = RabbitQueues::find()->where(['consumer_class_name' => $className])->one();
        $this->data = $queue->data_request ?? $this->data;
        $this->init();
        $count = $this->request['listOptions']['offset'];
        $this->log('Load' . PHP_EOL);
        $error = 0;
        $list = null;
        do {
            try {
                $response = $this->instance->sendRequest($this->method, $this->request);
                $list = $response->{$this->listName};
                $count += $list->count;
                $this->log('Load ' . $count . ' / ' . $list->total . PHP_EOL);
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
                $queue->data_request = json_encode($this->request);
                $queue->save();
                $this->log($e->getMessage() . " " . $e->getTraceAsString());
                mercLogger::getInstance()->addMercLogDict('ERROR', $this->modelClassName, $e->getMessage());
                $error++;
                if ($error == 3) {
                    die('Error operation');
                }
            }
        } while ($list->total > ($list->count + $list->offset));
        $queue->data_request = null;
        $queue->save();
        mercLogger::getInstance()->addMercLogDict('COMPLETE', $this->modelClassName, null);
    }

    /**
     * @return mixed
     */
    public function saveData()
    {
        return true;
    }
}