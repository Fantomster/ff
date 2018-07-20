<?php

use yii\db\Migration;

/**
 * Class m180614_161531_add_all_map
 */
class m180614_161531_add_all_map extends Migration
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
            '{{%all_map}}',
            [
                'id'=> $this->primaryKey(11),
                'service_id' => $this->integer(),
                'supp_id' => $this->integer(),
                'cat_id' => $this->integer(),
                'product_id' => $this->integer(),
                'product_rid' => $this->integer(),
                'org_id' => $this->integer(),
                'vat' => $this->integer(),
                'vat_included' => $this->integer(),
                'koef' => $this->double(),
                'store_rid' => $this->integer(),
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

    }


}
