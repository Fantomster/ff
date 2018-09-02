<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 26.08.2018
 * Time: 17:24
 */

namespace console\modules\daemons\classes;

use api\common\models\RabbitQueues;
use console\modules\daemons\components\MercDictConsumer;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\LoadStockEntryList;

/**
 * Class consumer with realization ConsumerInterface
 * and containing AbstractConsumer methods
 */
class MercStoreEntryList extends MercDictConsumer
{
    public function getData()
    {
        $queue = RabbitQueues::find()->where(['consumer_class_name' => 'MercVSDList_'.$this->org_id])->orderBy(['last_executed' => SORT_DESC])->one();

        $vsd = new LoadStockEntryList();
        $vsd->org_id = $this->org_id;
        $startDate =  ($queue === null) ?  date("Y-m-d H:i:s", mktime(0, 0, 0, 1, 1, 2000)): $queue->last_executed;
        $vsd->updateData($startDate);
    }
}