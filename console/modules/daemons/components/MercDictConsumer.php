<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 26.08.2018
 * Time: 17:24
 */

namespace console\modules\daemons\components;

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

    public function __construct($org_id = null)
    {
        $this->org_id = $org_id;
    }

    /**
     * Обработка и сохранение результата
     * @param $list
     */
    protected function saveList($list)
    {
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
        // TODO: Implement getData() method.
        $this->init();
        do {
            $response = $this->instance->sendRequest($this->method, $this->request);
            $list = $response->{$this->listName};
            if ($list->count > 0)
                $this->saveList($list->{$this->listItemName});

            if ($list->count < $list->total)
                $this->request->listOptions->offset += $list->count;

        } while ($list->total > ($list->count + $list->offset));
    }

    /**
     * @return mixed
     */
    public function saveData()
    {
        // TODO: Implement saveData() method.
        return $this->result;
    }
}