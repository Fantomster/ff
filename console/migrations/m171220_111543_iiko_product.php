<?php

use yii\db\Migration;

/**
 * Class m171220_111543_iiko_product
 */
class m171220_111543_iiko_product extends Migration
{

    /**
    [0efe118e-0870-4250-842e-b7e821176b62] =>
     * Array (
     * [parentId] => 0347a01d-e7a4-485d-b6f4-e23d4ccd59f9
     * [num] => 00008
     * [code] => 11
     * [name] => Сатал Цезарь
     * [productType] => DISH
     * [cookingPlaceType] => Кухня
     * [mainUnit] => порц
     * [containers] => Array ( )
     * )
     **/
    /**
     * @inheritdoc
     */

    public $tableName = '{{%iiko_product}}';

    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'uuid' => $this->string(36)->notNull(),
            'denom' => $this->string(),
            'parent_uuid' => $this->string(36)->null(),
            'org_id' => $this->integer()->notNull(),
            'num' => $this->string(50)->null(),
            'code' => $this->string(50)->null(),
            'product_type' => $this->string(50)->null(),
            'cooking_place_type' => $this->string(50)->null(),
            'unit' => $this->string(50)->null(),
            'containers' => $this->text()->null(),
            'is_active' => $this->integer(1)->defaultValue(1),
            'created_at' => $this->timestamp()->defaultValue(new \yii\db\Expression('NOW()')),
            'updated_at' => $this->timestamp()->null()
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
