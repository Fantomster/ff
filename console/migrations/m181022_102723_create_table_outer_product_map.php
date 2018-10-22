<?php

use yii\db\Migration;

/**
 * Class m181022_102723_create_table_outer_product_map
 */
class m181022_102723_create_table_outer_product_map extends Migration
{
    public $tableName = '{{%outer_product_map}}';

    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            $this->tableName,
            [
                'id' => $this->primaryKey(11)->comment('первичный ключ'),
                'created_at' => $this->datetime()->null()->defaultValue(null)->comment('Дата создания записи'),
                'updated_at' => $this->datetime()->null()->defaultValue(null)->comment('Дата последнего изменения записи'),
                'service_id' => $this->integer()->notNull()->comment('id сервиса из таблицы all_service'),
                'organization_id' => $this->integer()->notNull()->comment('id ресторана'),
                'vendor_id' => $this->integer()->notNull()->comment('id поставщика'),
                'product_id' => $this->integer()->notNull()->comment('id продукта в MC'),
                'outer_product_id' => $this->integer()->null()->defaultValue(null)->comment('id продукта из у.с. таблицы outer_product'),
                'outer_unit_id' => $this->integer()->null()->defaultValue(null)->comment('id единицы измерения у.с. таблицы outer_unit'),
                'outer_store_id' => $this->integer()->null()->defaultValue(null)->comment('id склада у.с. таблицы outer_store'),
                'coefficient' => $this->float()->notNull()->defaultValue(1)->comment('коэффициент'),
                'vat' => $this->float()->notNull()->defaultValue(0)->comment('НДС'),
            ], $tableOptions
        );

        $this->addForeignKey('all_service_id_fk', $this->tableName, 'service_id', '{{%all_service}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('outer_product_id_fk', $this->tableName, 'outer_product_id', '{{%outer_product}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('outer_unit_id_fk', $this->tableName, 'outer_unit_id', '{{%outer_unit}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('outer_store_id_fk', $this->tableName, 'outer_store_id', '{{%outer_store}}', 'id', 'CASCADE', 'CASCADE');

    }

    public function safeDown()
    {
        $this->dropForeignKey('all_service_id_fk', $this->tableName);
        $this->dropForeignKey('outer_product_id_fk', $this->tableName);
        $this->dropForeignKey('outer_unit_id_fk', $this->tableName);
        $this->dropForeignKey('outer_store_id_fk', $this->tableName);
        $this->dropTable($this->tableName);
    }
}
