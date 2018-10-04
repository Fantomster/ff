<?php

/**
 * Class Migration
 * @package api_web\classes
 * @createdBy Basil A Konakov
 * @createdAt 2018-10-02
 * @author Mixcart
 * @module WEB-API
 * @version 2.0
 */

use yii\db\Migration;

class m181002_085448_outer_dictionaries extends Migration
{

    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {

        $this->createTable('{{%outer_dictionary}}', [
            'id' => $this->primaryKey()->comment('Идентификатор словаря'),
            'name' => $this->string(255)->notNull()->comment('Наименование словаря'),
            'service_id' => $this->integer()->notNull()->comment('Код сервиса'),
        ]);
        $this->addForeignKey('{{%outer_dictionary_service}}', '{{%outer_dictionary}}', 'service_id', '{{%all_service}}', 'id');

        $this->createTable('{{%organization_dictionary}}', [
            'id' => $this->primaryKey()->comment('Идентификатор записи'),
            'outer_dic_id' => $this->integer()->notNull()->comment('Код словаря'),
            'org_id' => $this->integer()->notNull()->comment('Код организации'),
            'status_id' => $this->tinyInteger(1)->null()->comment('ID статуса - выгружен, ошибка, не выгружался'),
            'count' => $this->integer()->comment('Количество записей в словаре'),
            'created_at' => $this->timestamp()->null()->comment('Дата создания'),
            'updated_at' => $this->timestamp()->null()->comment('Дата обновления'),
        ]);

        $this->addForeignKey('{{%organization_dictionary_outer_dic}}', '{{%organization_dictionary}}', 'outer_dic_id', '{{%outer_dictionary}}', 'id');

    }

    public function safeDown()
    {

    }

}
