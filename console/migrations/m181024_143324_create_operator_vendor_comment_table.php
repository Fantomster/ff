<?php

use yii\db\Migration;

/**
 * Handles the creation of table `operator_vendor_comment`.
 */
class m181024_143324_create_operator_vendor_comment_table extends Migration
{
    public $tableName = '{{%operator_vendor_comment}}';

    public function init()
    {
        $this->db = 'db';
        parent::init();
    }

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            $this->tableName,
            [
                'vendor_id' => $this->primaryKey(11)->comment('id поставщика'),
                'comment' => $this->string(300)->null()->comment('комментарий'),
                'created_at' => $this->datetime()->null()->defaultValue(null)->comment('Дата создания записи'),
                'updated_at' => $this->datetime()->null()->defaultValue(null)->comment('Дата последнего изменения записи'),
            ], $tableOptions
        );

        $this->addForeignKey('organization_id_fk', $this->tableName, 'vendor_id', '{{%organization}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropForeignKey('organization_id_fk', $this->tableName);
        $this->dropTable($this->tableName);
    }
}
