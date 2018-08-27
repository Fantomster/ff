<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 26.08.2018
 * Time: 17:24
 */

namespace console\modules\daemons\classes;

use console\modules\daemons\components\AbstractConsumer;
use console\modules\daemons\components\ConsumerInterface;

/**
 * Class consumer with realization ConsumerInterface
 * and containing AbstractConsumer methods
 */
class Merc extends AbstractConsumer implements ConsumerInterface
{
    
    /**
     * @return mixed
     */
    public function getData()
    {
        // TODO: Implement getData() method.
    }
    
    /**
     * @return mixed
     */
    public function saveData()
    {
        // TODO: Implement saveData() method.
        return true;
    }
}