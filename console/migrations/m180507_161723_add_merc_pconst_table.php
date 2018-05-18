<?php

use yii\db\Migration;

/**
 * Class m180507_161723_add_merc_pconst_table
 */
class m180507_161723_add_merc_pconst_table extends Migration
{
    public $tableName = '{{%merc_pconst}}';

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
        $sql = "CREATE TABLE {$this->tableName}
                (
                  id         INT AUTO_INCREMENT PRIMARY KEY,
                  const_id   INT          NULL,
                  org        INT          NULL,
                  value      VARCHAR(255) NULL,
                  created_at DATETIME     NULL,
                  updated_at DATETIME     NULL
                );
        ";
        $this->execute($sql);

        $this->createIndex('idx_merc_const', $this->tableName, 'const_id');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx_merc_const', $this->tableName);
        $this->dropTable($this->tableName);
    }


}
