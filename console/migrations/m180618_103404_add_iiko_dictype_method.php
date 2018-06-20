<?php

use yii\db\Migration;

class m180618_103404_add_iiko_dictype_method extends Migration
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
        $this->addColumn('{{%iiko_dictype}}', 'method', $this->string()->null()->comment('Метод в модели iikoSync для запуска синхронизации'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%iiko_dictype}}', 'method');
    }
}
