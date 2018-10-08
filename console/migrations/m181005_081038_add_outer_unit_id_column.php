<?php

use yii\db\Migration;

/**
 * Class m181005_081038_add_outer_unit_id_column
 */
class m181005_081038_add_outer_unit_id_column extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }


    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn("{{%waybill_content}}", 'outer_unit_id', $this->integer()->null());
        $this->addCommentOnColumn("{{%waybill_content}}", 'outer_unit_id', "Идентификатор единицы измерения во внешней системе");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn("{{%waybill_content}}", 'outer_unit_id');
    }
}
