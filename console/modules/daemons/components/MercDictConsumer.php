<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 26.08.2018
 * Time: 17:24
 */

namespace console\modules\daemons\components;

use api\common\models\merc\mercPconst;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercLogger;

/**
 * Class consumer with realization ConsumerInterface
 * and containing AbstractConsumer methods
 */
class MercDictConsumer extends AbstractConsumer implements ConsumerInterface
{
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
            $model->setAttributes($attributes);
            $model->data = serialize($item);
            $model->createDate = date('Y-m-d H:i:s', strtotime($model->createDate));
            $model->updateDate = date('Y-m-d H:i:s', strtotime($model->updateDate));
            if (!$model->save()) {
                $result[]['error'] = $model->getErrors();
                $result[]['model-data'] = $model->attributes;
            }
        }

        if (empty($result)) {
            mercLogger::getInstance()->addMercLogDict('COMPLETE', $this->modelClassName, null);
        } else {
            mercLogger::getInstance()->addMercLogDict('ERROR', $this->modelClassName, json_encode($result));
        }
    }

    /**
     * инициализация свойств класса
     */
    protected function init()
    {
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        $this->init();
        $count = 0;
        $this->log('Load' . PHP_EOL);
        try {
            do {
                $response = $this->instance->sendRequest($this->method, $this->request);
                $list = $response->{$this->listName};
                $count += $list->count;
                $this->log('Load ' . $count . ' / ' . $list->total . PHP_EOL);
                if ($list->count > 0) {
                    $this->saveList($list->{$this->listItemName});
                }

                if ($list->count < $list->total) {
                    $this->request['listOptions']['offset'] += $list->count;
                }
            } while ($list->total > ($list->count + $list->offset));
        }catch (\Throwable $e)
        {
            $this->log($e->getMessage());
        }
    }

    /**
     * @return mixed
     */
    public function saveData()
    {
        return true;
    }
}