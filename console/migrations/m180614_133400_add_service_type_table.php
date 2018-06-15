<?php

use yii\db\Migration;

/**
 * Class m180614_133400_add_service_type_table
 */
class m180614_133400_add_service_type_table extends Migration
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
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%all_service_type}}',
            [
                'id'=> $this->primaryKey(11),
                'denom' => $this->string(255),
                'is_active' => $this->integer(),
                'created_at' => $this->datetime()->null()->defaultValue(null),
                'updated_at' => $this->datetime()->null()->defaultValue(null)
            ],$tableOptions
        );

        $this->batchInsert('{{%all_service_type}}', ['id','denom','is_active'], [
            [1, 'Учетная система ресторан',1],
            [2, 'Учетная система поставщик',1],
            [3, 'Государственный реестр', 1],
            [4, 'Электронный документооборот', 1],
            [5, 'Внутренний сервис', 1],
        ]);

    }


    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%all_service_type}}');
    }


}
