<?php

use yii\db\Migration;
use common\models\OrderStatus;
use common\components\EchoRu;

/**
 * Class m180913_114107_fix_constant_name_for_order_status
 */
class m180913_114107_fix_constant_name_for_order_status extends Migration
{

    public function safeUp()
    {
        $os = OrderStatus::findOne(9);
        $os->denom = 'STATUS_EDO_ACCEPTANCE_FINISHED';
        $os->comment =  'common.models.order_status.status_edo_acceptance_finished';
        $os->save();
        EchoRu::echo ($os->comment, 'Приемка завершена', 'app');
    }

    public function safeDown()
    {
    }

}
