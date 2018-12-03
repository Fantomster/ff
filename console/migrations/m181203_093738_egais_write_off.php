<?php

use yii\db\Migration;

/**
 * Class m181203_093738_egais_write_off
 */
class m181203_093738_egais_write_off extends Migration
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
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%egais_write_off}}', [
            'id' => $this->primaryKey(),
            'org_id' => $this->integer()->notNull(),
            'identity' => $this->integer(),
            'act_number' => $this->integer(),
            'act_date' => $this->string(250),
            'type_write_off' => $this->integer()->notNull(),
            'note' => $this->string(250)->null(),
            'status' => $this->string(250)->null(),
            'created_at' => $this->timestamp()->defaultValue(new \yii\db\Expression('NOW()')),
            'updated_at' => $this->timestamp()->null()
        ], $tableOptions);

        $this->addForeignKey(
            '{{%egais_write_off_type}}',
            '{{%egais_write_off}}',
            'type_write_off',
            '{{%egais_type_write_off}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%egais_write_off_type}}', '{{%egais_write_off}}');
        $this->dropTable('{{%egais_write_off}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181203_093738_egais_write_off cannot be reverted.\n";

        return false;
    }
    */
}
