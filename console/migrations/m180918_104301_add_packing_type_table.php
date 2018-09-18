<?php

use yii\db\Migration;

/**
 * Class m180918_104301_add_packing_type_table
 */
class m180918_104301_add_packing_type_table extends Migration
{
    public function init()
    {
        $this->db = "db_api";
        parent::init();
    }

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%vetis_packing_type}}',
            [
                'uuid'=> $this->string(255)->unique()->notNull()->comment('Идентификатор версии типа упаковки'),
                'guid'=> $this->string(255)->notNull()->comment('Глобальный идентификатор упаковки'),
                'name'=> $this->string(255)->notNull()->comment('Наименование упаковки'),
                'globalID'=> $this->string(2)->notNull()->comment('Уникальный идентификатор упаковки'),
            ],$tableOptions
        );

        $this->createIndex('vetis_packing_type_uuid', '{{%vetis_packing_type}}', 'uuid');
        $this->createIndex('vetis_packing_type_guid', '{{%vetis_packing_type}}', 'guid');
    }

    public function safeDown()
    {
        $this->dropIndex('vetis_packing_type_uuid', '{{%vetis_packing_type}}');
        $this->dropIndex('vetis_packing_type_guid', '{{%vetis_packing_type}}');
        $this->dropTable('{{%vetis_packing_type}}');
    }
}
