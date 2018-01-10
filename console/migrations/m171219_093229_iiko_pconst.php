<?php

use yii\db\Migration;

/**
 * Class m171219_093229_iiko_pconst
 */
class m171219_093229_iiko_pconst extends Migration
{
    public $tableName = '{{%iiko_pconst}}';

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

        $this->createIndex('idx_iiko_const', $this->tableName, 'const_id');
        $this->addForeignKey(
            'fk-iiko-const',
            $this->tableName,
            'const_id',
            \api\common\models\iiko\iikoDicconst::tableName(),
            'id'
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-iiko-const', $this->tableName);
        $this->dropIndex('idx_iiko_const', $this->tableName);
        $this->dropTable($this->tableName);
    }
}
