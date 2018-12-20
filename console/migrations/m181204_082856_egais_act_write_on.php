<?php

use yii\db\Migration;

/**
 * Class m181204_082856_egais_act_write_on
 */
class m181204_082856_egais_act_write_on extends Migration
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

        $this->createTable('{{%egais_type_charge_on}}', [
            'id' => $this->primaryKey(),
            'type' => $this->string()->notNull()
        ], $tableOptions);

        $this->batchInsert('{{%egais_type_charge_on}}', ['type'], [
            ['Пересортица'],
            ['Излишки'],
            ['Продукция, полученная до 01.01.2016'],
            ['Производство_Сливы'],
        ]);

        $this->createTable('{{%egais_act_write_on}}', [
            'id' => $this->primaryKey(),
            'org_id' => $this->integer()->notNull(),
            'number' => $this->integer(),
            'act_date' => $this->string(250),
            'note' => $this->string(250)->null(),
            'type_charge_on' => $this->integer()->notNull(),
            'status' => $this->string(250)->null(),
            'reply_id' => $this->string(250)->null(),
            'created_at' => $this->timestamp()->defaultValue(new \yii\db\Expression('NOW()')),
            'updated_at' => $this->timestamp()->null()
        ], $tableOptions);

        $this->addForeignKey(
            '{{%egais_act_write_on_type}}',
            '{{%egais_act_write_on}}',
            'type_charge_on',
            '{{%egais_type_charge_on}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%egais_act_write_on_type}}', '{{%egais_act_write_on}}');
        $this->dropTable('{{%egais_act_write_on}}');
        $this->dropTable('{{%egais_type_charge_on}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181204_082856_egais_act_write_on cannot be reverted.\n";

        return false;
    }
    */
}
