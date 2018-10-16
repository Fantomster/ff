<?php

use yii\db\Migration;
use common\models\OrderStatus;

/**
 * Class m180912_150538_tbl_order_status_upgrade
 */
class m180912_150538_tbl_order_status_upgrade extends Migration
{

// переписать без модели
    public function safeUp()
    {

        $commentPrefix = 'common.models.order_status.';

        /** @var $os OrderStatus */
        $os = OrderStatus::findOne(1);
        $os->comment = $commentPrefix . 'status_awaiting_accept_from_vendor';
        $os->save();

        $os = OrderStatus::findOne(2);
        $os->comment = $commentPrefix . 'status_awaiting_accept_from_client';
        $os->save();

        $os = OrderStatus::findOne(3);
        $os->comment = $commentPrefix . 'status_processing';
        $os->save();

        $os = OrderStatus::findOne(4);
        $os->comment = $commentPrefix . 'status_done';
        $os->save();

        $os = OrderStatus::findOne(5);
        $os->comment = $commentPrefix . 'status_rejected';
        $os->save();

        $os = OrderStatus::findOne(6);
        $os->comment = $commentPrefix . 'status_cancelled';
        $os->save();

        $os = OrderStatus::findOne(7);
        $os->comment = $commentPrefix . 'status_forming';
        $os->save();

        $os = OrderStatus::findOne(8);
        $os->denom = 'STATUS_EDO_SENT_BY_VENDOR';
        $os->comment = $commentPrefix . 'status_edo_sent_by_vendor';
        $os->save();

        $os = OrderStatus::findOne(9);
        $os->denom = 'STATUS_EDO_RECADV_SENT';
        $os->comment = $commentPrefix . 'status_edo_recadv_sent';
        $os->save();
    }

    public function safeDown()
    {
        
    }

}
