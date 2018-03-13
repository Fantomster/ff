<?php

use yii\db\Migration;

/**
 * Class m180311_172121_addConstant_useWinEncoding
 */
class m180311_172121_addConstant_useWinEncoding extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->insert('{{%rk_dicconst}}',[ 'denom' => 'useWinEncoding', 'def_value' =>'0', 'comment' => 'Автоматическая конвертация значений из CP-1252 (CP-1251) в UTF-8 при загрузке','type'=>1, 'is_active' => 1]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->delete('{{%rk_dicconst}}',['denom' =>'useWinEncoding']);
    }
}
