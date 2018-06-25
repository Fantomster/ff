<?php

use yii\db\Migration;

/**
 * Class m180622_182520_add_odinsobsh_pconst_table
 */
class m180622_182520_add_odinsobsh_pconst_table extends Migration
{
    public $tableName = '{{%one_s_pconst}}';

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

        $this->createIndex('idx_one_s_const', $this->tableName, 'const_id');
        $this->addForeignKey(
            'fk-ones-const',
            $this->tableName,
            'const_id',
            \api\common\models\one_s\OneSDicconst::tableName(),
            'id'
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-ones-const', $this->tableName);
        $this->dropIndex('idx_one_s_const', $this->tableName);
        $this->dropTable($this->tableName);
    }
}
