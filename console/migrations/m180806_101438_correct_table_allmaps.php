<?php

use yii\db\Migration;

/**
 * Class m180806_101438_correct_table_allmaps
 */
class m180806_101438_correct_table_allmaps extends Migration
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
        $this->dropTable('{{%all_map}}');

        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%all_map}}',
            [
                'id'=> $this->primaryKey(11),
                'service_id' => $this->integer(),
                'org_id' => $this->integer(),
                'product_id' => $this->integer(),
                'supp_id' => $this->integer(),
                'serviceproduct_id' => $this->integer(),
                'unit_rid' => $this->integer(),
                'store_rid' => $this->integer(),
                'koef' => $this->double(),
                'vat' => $this->integer(),
                'is_active' => $this->integer(),
                'created_at' => $this->datetime()->null()->defaultValue(null),
                'linked_at' => $this->datetime()->null()->defaultValue(null),
                'updated_at' => $this->datetime()->null()->defaultValue(null)
            ],$tableOptions
        );



    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        'Cannot be rollback. But it is OK';
        return true;
    }


}
