<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 26.08.2018
 * Time: 17:24
 */

namespace console\modules\daemons\components;

use frontend\modules\clientintegr\modules\merc\helpers\api\dicts\ListOptions;

/**
 * Class consumer with realization ConsumerInterface
 * and containing AbstractConsumer methods
 */
class MercDictConsumer extends AbstractConsumer implements ConsumerInterface
{
    protected $result = true;
    protected $count = 100;
    protected $instance;
    protected $method;
    protected $listName;
    protected $listItemName;

    /**
     * Обработка и сохранение результата
     * @param $list
     */
    protected function saveList($list) {
    }

    /**
     * инициализация свойств класса
     */
    protected function init(){
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        // TODO: Implement getData() method.
        $this->init();
        $listOptions = new ListOptions();
        $listOptions->count = $this->count;
        $listOptions->offset = 0;

        do {
           $response = $this->instance->{$this->method}($listOptions);
           $list = $response->{$this->listName};

            if($list->count > 0)
               $this->saveList($list->{$this->listItemName});

            if($list->count < $list->total)
                $listOptions->offset += $list->count;

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