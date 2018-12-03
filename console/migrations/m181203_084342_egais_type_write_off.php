<?php

use yii\db\Migration;

/**
 * Class m181203_084342_egais_type_write_off
 */
class m181203_084342_egais_type_write_off extends Migration
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
        $this->createTable('{{%egais_type_write_off}}', [
            'id' => $this->primaryKey(),
            'type' => $this->string()->notNull(),
        ], $tableOptions);

        $this->batchInsert('{{%egais_type_write_off}}', ['type'], [
            ['Пересортица'],
            ['Недостача'],
            ['Уценка'],
            ['Порча'],
            ['Потери'],
            ['Проверки'],
            ['Арест'],
            ['Иные цели'],
            ['Реализация'],
            ['Производственные потери'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%egais_type_write_off}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181203_084342_egais_type_write_off cannot be reverted.\n";

        return false;
    }
    */
}
