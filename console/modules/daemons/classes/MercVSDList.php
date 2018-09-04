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
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\VetDocumentsChangeList;

/**
 * Class consumer with realization ConsumerInterface
 * and containing AbstractConsumer methods
 */
class MercVSDList extends MercDictConsumer
{
    public static $timeout  = 60*5;
    public static $timeoutExecuting = 60*60;

    public function getData()
    {
        $check = RabbitQueues::find()->where("consumer_class_name in ('MercUnitList', 'MercPurposeList', 
        'MercCountryList', 'MercRussianEnterpriseList', 'MercForeignEnterpriseList', 'MercBusinessEntityList', 'MercProductList', 'MercProductItemList', 'MercSubProductList')")
        ->andWhere('start_executing is not null')->one();

        if($check == null) {
            return true;
        }

        $queue = RabbitQueues::find()->where(['consumer_class_name' => 'MercVSDList_'.$this->org_id])->orderBy(['last_executed' => SORT_DESC])->one();
        $vsd = new VetDocumentsChangeList();
        $vsd->org_id = $this->org_id;
        $startDate =  ($queue->last_executed === null) ?  date("Y-m-d H:i:s", mktime(0, 0, 0, 1, 1, 2000)): $queue->last_executed;
        $vsd->updateData($startDate);
    }
}