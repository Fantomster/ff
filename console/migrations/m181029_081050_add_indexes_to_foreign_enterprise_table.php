<?php

use yii\db\Migration;

/**
 * Class m181029_081050_add_indexes_to_foreign_enterprise_table
 */
class m181029_081050_add_indexes_to_foreign_enterprise_table extends Migration
{

    public function init()
    {
        $this->db = "db_api";
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        try {
            $this->dropTable('vetis_foreign_enterprise_old');
        } catch (\Exception $e) {
            //
        }
//Создаем новую таблицу
        $newTable = '_vetis_foreign_enterprise';
        $sql      = "
        CREATE TABLE $newTable
        (
            uuid varchar(36) not null primary key,
            guid varchar(255) not null,
            last tinyint(1) null,
            active tinyint(1) null,
            type int null,
            next varchar(255) null,
            previous varchar(255) null,
            name varchar(255) null,
            inn varchar(255) null,
            kpp varchar(255) null,
            country_guid varchar(255) null,
            addressView text null,
            data text null,
            owner_guid varchar(255) null comment 'Глобальный идентификатор хозяйствующего субъекта владельца',
            owner_uuid varchar(255) null comment 'Идентификатор хозяйствующего субъекта владельца',
            constraint uuid unique (uuid) 
        );

        create index vetis_foreign_enterprise_guid on $newTable (guid);
        create index vetis_foreign_enterprise_uuid on $newTable (uuid);
        create index vetis_foreign_enterprise_owner_uuid on $newTable (owner_uuid);
        create index vetis_foreign_enterprise_owner_guid on $newTable (owner_guid);
        ALTER TABLE $newTable ADD FULLTEXT INDEX vetis_foreign_enterprise_name (name ASC);
        ";
        $this->execute($sql);

        //Переносим данные
        $sql = "INSERT INTO $newTable SELECT * FROM vetis_foreign_enterprise";
        $this->execute($sql);

        //переименовываем
        $this->renameTable('vetis_foreign_enterprise', 'vetis_foreign_enterprise_old');
        $this->renameTable('_vetis_foreign_enterprise', 'vetis_foreign_enterprise');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181029_081050_add_indexes_to_foreign_enterprise_table cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181029_081050_add_indexes_to_foreign_enterprise_table cannot be reverted.\n";

        return false;
    }
    */
}
