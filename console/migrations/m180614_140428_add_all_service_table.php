<?php

use yii\db\Migration;

/**
 * Class m180614_140428_add_all_service_table
 */
class m180614_140428_add_all_service_table extends Migration
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
            '{{%all_service}}',
            [
                'id'=> $this->primaryKey(11),
                'type_id' => $this->integer(),
                'is_active' => $this->integer(),
                'denom' => $this->string(255),
                'vendor' => $this->string(255),
                'created_at' => $this->datetime()->null()->defaultValue(null),
                'updated_at' => $this->datetime()->null()->defaultValue(null)
            ],$tableOptions
        );

        $this->batchInsert('{{%all_service}}', ['id','type_id','is_active','denom','vendor'], [
            [1,1,1,'R-keeper','UCS'],
            [2,1,1,'iiko','iiko'],
            [3,2,1,'Накладные поставщика',''],
            [4,3,1,'ВЕТИС Меркурий','Россельхознадзор'],
            [5,3,1,'ЕГАИС','Росалкогольрегулирование'],
            [6,4,1,'EDI','ECOM'],
            [7,2,1,'1C-поставщик','1С'],
            [8,1,1,'1C-ресторан','1С'],
            [9,5,1,'Внутренний Back-end','Mixcart'],
        ]);

    }


    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%all_service}}');
    }

}
