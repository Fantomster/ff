<?php

use yii\db\Migration;

/**
 * Class m181115_114717_add_price_column_to_license_organization
 */
class m181115_114717_add_price_column_to_license_organization extends Migration
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
        $this->addColumn("license_organization", 'price', $this->decimal(10, 2)->defaultValue(0.00));
        $this->addCommentOnColumn("license_organization", 'price', 'Стоимость лицензии');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn("license_organization", 'price');
    }

}
