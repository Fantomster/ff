<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 26.08.2018
 * Time: 17:24
 */

namespace console\modules\daemons\components;

use api\common\models\merc\mercPconst;

/**
 * Class consumer with realization ConsumerInterface
 * and containing AbstractConsumer methods
 */
class MercDictConsumer extends AbstractConsumer implements ConsumerInterface
{
    protected $result = true;
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
        if($org_id != null) {
            $this->org_id = $org_id;
        }
        else {
           $this->org_id = (mercPconst::findOne())->org;
        }

    }

    /**
     * Обработка и сохранение результата
     * @param $list
     */
    protected function saveList($list)
    {
        $list = is_array($list) ? $list : [$list];
        foreach ($list as $item)
        {
            $model = $this->modelClassName::findOne(['guid' => $item->guid]);

            if($model == null) {
                $model = new $this->modelClassName();
            }
            $attributes =  json_decode(json_encode($item), true);
            $model->setAttributes($attributes);
            $model->createDate = date('Y-m-d H:i:s',strtotime($model->createDate));
            $model->updateDate = date('Y-m-d H:i:s',strtotime($model->updateDate));
            if (!$model->save()) {
                $this->result = false;
            }
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
        do {
            $response = $this->instance->sendRequest($this->method, $this->request);
            $list = $response->{$this->listName};
            if ($list->count > 0) {
                $this->saveList($list->{$this->listItemName});
            }

            if ($list->count < $list->total) {
                $this->request->listOptions->offset += $list->count;
            }

        } while ($list->total > ($list->count + $list->offset));
    }

    /**
     * @return mixed
     */
    public function saveData()
    {
        return $this->result;
    }
}