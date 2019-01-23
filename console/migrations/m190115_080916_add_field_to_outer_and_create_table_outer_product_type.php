<?php

use api_web\components\Registry;
use api_web\exceptions\ValidationException;
use common\models\OuterProductType;
use yii\db\Migration;
use yii\db\Schema;

/**
 * Class m190115_080916_add_field_to_outer_and_create_table_outer_product_type
 */
class m190115_080916_add_field_to_outer_and_create_table_outer_product_type extends Migration
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
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable("{{%outer_product_type}}", [
            'id'         => Schema::TYPE_PK,
            'service_id' => Schema::TYPE_INTEGER,
            'value'      => Schema::TYPE_STRING,
            'comment'    => Schema::TYPE_STRING,
        ], $tableOptions);

        $this->addColumn('{{%outer_product}}', 'outer_product_type_id', $this->integer(11)->null());
        $this->addForeignKey('{{%fk_outer_product_type}}', '{{%outer_product}}', 'outer_product_type_id', '{{%outer_product_type}}', 'id');

        $ar = [
            1 => 'товар',
            4 => 'ингредиент',
            5 => 'модификатор товара',
        ];
        foreach ($ar as $key => $value) {
            $model = new OuterProductType([
                'service_id' => Registry::POSTER_SERVICE_ID,
                'value'      => (string)$key,
                'comment'    => $value,
            ]);
            if (!$model->save()) {
                throw new ValidationException($model->getFirstErrors());
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190115_080916_add_field_to_outer_and_create_table_outer_product_type cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190115_080916_add_field_to_outer_and_create_table_outer_product_type cannot be reverted.\n";

        return false;
    }
    */
}
