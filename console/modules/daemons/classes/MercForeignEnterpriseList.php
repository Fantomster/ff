<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 26.08.2018
 * Time: 17:24
 */

namespace console\modules\daemons\classes;

use common\models\vetis\VetisForeignEnterprise;
use console\modules\daemons\components\MercDictConsumer;
use frontend\modules\clientintegr\modules\merc\helpers\api\cerber\cerberApi;

/**
 * Class consumer with realization ConsumerInterface
 * and containing AbstractConsumer methods
 */
class MercForeignEnterpriseList extends MercDictConsumer
{
    protected function init()
    {
        $this->instance = cerberApi::getInstance($this->org_id);
        $data = json_decode($this->data, true);
        $this->method = $data['method'];
        $this->request = json_decode($data['request'], true);
        $this->listName = $data['struct']['listName'];
        $this->listItemName = $data['struct']['listItemName'];
        $this->modelClassName = VetisForeignEnterprise::class;
    }
}