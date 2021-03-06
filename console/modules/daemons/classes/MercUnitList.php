<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 26.08.2018
 * Time: 17:24
 */

namespace console\modules\daemons\classes;
use common\models\vetis\VetisUnit;
use console\modules\daemons\components\MercDictConsumer;
use frontend\modules\clientintegr\modules\merc\helpers\api\dicts\dictsApi;

/**
 * Class consumer with realization ConsumerInterface
 * and containing AbstractConsumer methods
 */
class MercUnitList extends MercDictConsumer
{
    public static $timeout  = 60*60*24;
    public static $timeoutExecuting = 60*5;

    protected function init()
    {
        parent::init();
        $this->instance = dictsApi::getInstance($this->org_id);
        $this->modelClassName = VetisUnit::class;
    }
}