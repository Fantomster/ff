<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 26.08.2018
 * Time: 17:24
 */

namespace console\modules\daemons\classes;

use common\models\vetis\VetisPurpose;
use console\modules\daemons\components\MercDictConsumer;
use frontend\modules\clientintegr\modules\merc\helpers\api\dicts\dictsApi;

/**
 * Class consumer with realization ConsumerInterface
 * and containing AbstractConsumer methods
 */
class MercPurposeList extends MercDictConsumer
{
    protected function init()
    {
        $this->instance = dictsApi::getInstance($this->org_id);
        $data = json_decode($this->data, true);
        $this->method = $data['method'];
        $this->request = $data['request'];
        $this->listName = $data['struct']['listName'];
        $this->listItemName = $data['struct']['listItemName'];
        $this->modelClassName = VetisPurpose::class;
    }
}