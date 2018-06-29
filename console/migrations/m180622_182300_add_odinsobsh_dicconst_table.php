<?php

use yii\db\Migration;

/**
 * Class m180622_182300_add_odinsobsh_dicconst_table
 */
class m180622_182300_add_odinsobsh_dicconst_table extends Migration
{
    public $tableName = '{{%one_s_dicconst}}';
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS {$this->tableName}
            (
              id        INT AUTO_INCREMENT PRIMARY KEY,
              denom     VARCHAR(255) NULL,
              def_value VARCHAR(255) NULL,
              comment   VARCHAR(255) NULL,
              type      INT          NULL,
              is_active INT(2)       NULL
            );
        ";
        $this->execute($sql);


        $this->insert($this->tableName, [
            'denom' => 'taxVat',
            'def_value' => '0',
            'comment' => 'Ставка НДС по-умолчанию',
            'type' => 1,
            'is_active' => 1
        ]);

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
