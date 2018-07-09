<?php

use yii\db\Migration;

/**
 * Class m180709_134000_iiko_method
 */
class m180709_134000_iiko_method extends Migration
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
        $this->update('{{%iiko_dictype}}', ['method' => 'agent'], "denom = 'Контрагенты'");
        $this->update('{{%iiko_dictype}}', ['method' => 'store'], "denom = 'Склады'");
        $this->update('{{%iiko_dictype}}', ['method' => 'category'], "denom = 'Категории'");
        $this->update('{{%iiko_dictype}}', ['method' => 'goods'], "denom = 'Товары'");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180709_134000_iiko_method cannot be reverted.\n";
        return false;
    }
}
