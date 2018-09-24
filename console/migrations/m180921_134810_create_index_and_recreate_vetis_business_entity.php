<?php

use yii\db\Migration;

/**
 * Class m180921_134810_create_index_and_recreate_vetis_business_entity
 */
class m180921_134810_create_index_and_recreate_vetis_business_entity extends Migration
{
    public function init()
    {
        $this->db = "db_api";
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function up()
    {
        //Создаем новую таблицу
        $newTable = '_vetis_business_entity';
        $sql = "
        create table $newTable
        (
            uuid varchar(36) not null PRIMARY KEY,
            guid varchar(255) not null,
            last tinyint(1) null,
            active tinyint(1) null,
            type int null,
            next varchar(255) null,
            previous varchar(255) null,
            name varchar(255) null,
            fullname varchar(255) null,
            fio varchar(255) null,
            inn varchar(255) null,
            kpp varchar(255) null,
            addressView text null,
            data text null,
            constraint uuid unique (uuid)
        );
        
        create index vetis_business_entity_guid on $newTable (guid);
        create index vetis_business_entity_uuid on $newTable (uuid);
        ALTER TABLE $newTable ADD FULLTEXT INDEX vetis_business_entity_name (name ASC);";
        $this->execute($sql);

        //Переносим данные
        $sql = "INSERT INTO $newTable SELECT * FROM vetis_business_entity";
        $this->execute($sql);

        //переименовываем
        $this->renameTable('vetis_business_entity', 'vetis_business_entity_old');
        $this->renameTable($newTable, 'vetis_business_entity');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180921_134810_create_index_and_recreate_vetis_business_entity cannot be reverted.\n";

        return false;
    }
}
