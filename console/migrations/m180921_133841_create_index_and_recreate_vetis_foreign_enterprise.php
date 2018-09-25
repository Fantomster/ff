<?php

use yii\db\Migration;

/**
 * Class m180921_133841_create_index_and_recreate_vetis_foreign_enterprise
 */
class m180921_133841_create_index_and_recreate_vetis_foreign_enterprise extends Migration
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
        $newTable = '_vetis_foreign_enterprise';
        $sql = "
        create table $newTable
        (
            uuid varchar(36) not null primary key,
            guid varchar(255) not null,
            last tinyint(1) null,
            active tinyint(1) null,
            type int null,
            next varchar(255)  null,
            previous varchar(255) null,
            name varchar(255) null,
            inn varchar(255) null,
            kpp varchar(255) null,
            country_guid varchar(255) null,
            addressView text null,
            data text null,
            owner_guid varchar(255) null comment 'Глобальный идентификатор хозяйствующего субъекта',
            owner_uuid varchar(255) null,
            constraint uuid unique (uuid)
        );

        create index vetis_foreign_enterprise_guid on $newTable (guid);
        create index vetis_foreign_enterprise_uuid on $newTable (uuid);
        ALTER TABLE $newTable ADD FULLTEXT INDEX vetis_foreign_enterprise_name (name ASC);";
        $this->execute($sql);

        //Переносим данные
        $sql = "INSERT INTO $newTable SELECT * FROM vetis_foreign_enterprise";
        $this->execute($sql);

        //переименовываем
        $this->renameTable('vetis_foreign_enterprise', 'vetis_foreign_enterprise_old');
        $this->renameTable($newTable, 'vetis_foreign_enterprise');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180921_133841_create_index_and_recreate_vetis_foreign_enterprise cannot be reverted.\n";

        return false;
    }
}
