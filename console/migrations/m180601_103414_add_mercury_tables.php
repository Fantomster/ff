<?php

use yii\db\Migration;

/**
 * Class m180601_103414_add_mercury_tables
 */
class m180601_103414_add_mercury_tables extends Migration
{
    public $tableName = '{{%merc_vsd}}';
    public $tableName2 = '{{%merc_visits}}';

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
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            $this->tableName2,
            [
                'id' => $this->primaryKey(11),
                'org' => $this->integer(11)->null()->defaultValue(null),
                'last_visit' => $this->datetime()->null()->defaultValue(null),
            ], $tableOptions
        );

        $this->createTable(
            $this->tableName,
            [
                'id' => $this->primaryKey(11),
                'uuid' => $this->string(255)->null()->defaultValue(null),
                'number' => $this->string(255)->null()->defaultValue(null),
                'date_doc' => $this->datetime()->null()->defaultValue(null),
                'status' => $this->string(255)->null()->defaultValue(null),
                'product_name' => $this->string(255)->null()->defaultValue(null),
                'amount' => $this->decimal(10,3)->null()->defaultValue(null),
                'unit' => $this->string(255)->null()->defaultValue(null),
                'production_date' => $this->datetime()->null()->defaultValue(null),
                'recipient_name' => $this->string(255)->null()->defaultValue(null),
            ], $tableOptions
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
       return true;
    }
}
