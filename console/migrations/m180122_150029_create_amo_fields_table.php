<?php

use yii\db\Migration;

/**
 * Handles the creation of table `amo_fields`.
 */
class m180122_150029_create_amo_fields_table extends Migration
{


    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%amo_fields}}', [
            'id' => $this->primaryKey(),
            'amo_field' => $this->string(255)->null()->defaultValue(null),
            'responsible_user_id' => $this->integer()->notNull(),
            'pipeline_id' => $this->integer()->notNull(),
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%amo_fields}}');
    }
}
