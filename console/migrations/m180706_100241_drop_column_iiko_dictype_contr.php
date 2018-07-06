<?php

use yii\db\Migration;

/**
 * Class m180706_100241_drop_column_iiko_dictype_contr
 */
class m180706_100241_drop_column_iiko_dictype_contr extends Migration
{
    public $table = '{{%iiko_dictype}}';

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
        $this->dropColumn($this->table, 'contr');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn($this->table, 'contr', $this->string());
    }
}
