<?php

use yii\db\Migration;

class m180710_112039_recreate_table_rk_waybillstatus extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }
    public function safeUp()
    {
        $this->dropTable('{{%rk_waybillstatus}}');
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%rk_waybillstatus}}', [
            'id' => $this->primaryKey(),
            'denom' => $this->string(128)->null()->defaultValue(null),
            'comment' => $this->string()->null()->defaultValue(null)
        ], $tableOptions);
        $this->insert('{{%rk_waybillstatus}}', [
            'denom' => 'К выгрузке',
            'comment' => null
        ]);
        $this->insert('{{%rk_waybillstatus}}', [
            'denom' => 'Выгружено',
            'comment' => null
        ]);
        $this->insert('{{%rk_waybillstatus}}', [
            'denom' => 'Ошибка',
            'comment' => null
        ]);
        $this->insert('{{%rk_waybillstatus}}', [
            'denom' => 'Запрос отправлен',
            'comment' => null
        ]);
    }

    public function safeDown()
    {
        echo "m180615_103356_recreate_table_rk_dicstatus cannot be reverted.\n";

        return false;
    }

}
