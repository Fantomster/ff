<?php

use yii\db\Migration;

/**
 * Class m181210_092144_egais_product_on_balance
 */
class m181210_092144_egais_product_on_balance extends Migration
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
        $this->createTable('{{%egais_product_on_balance}}', [
            'id' => $this->primaryKey(),
            'org_id' => $this->integer()->notNull(),
            'quantity' => $this->decimal(10, 4),
            'inform_a_reg_id' => $this->string(),
            'inform_b_reg_id' => $this->string(),
            'full_name' => $this->string(),
            'alc_code' => $this->string(),
            'capacity' => $this->decimal(10, 4),
            'alc_volume' => $this->decimal(10, 3),
            'product_v_code' => $this->integer(),
            'producer_client_reg_id' => $this->string(),
            'producer_inn' => $this->string(),
            'producer_kpp' => $this->string(),
            'producer_full_name' => $this->string(),
            'producer_short_name' => $this->string(),
            'address_country' => $this->integer(),
            'address_region_code' => $this->integer(),
            'address_description' => $this->text(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%egais_product_on_balance}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181210_092144_egais_product_on_balance cannot be reverted.\n";

        return false;
    }
    */
}
