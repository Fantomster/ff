<?php

use yii\db\Migration;

/**
 * Class m180215_081529_add_slug_to_salefish_category
 */
class m180215_081529_add_slug_to_salefish_category extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $query = "SELECT id FROM mp_category WHERE name = :name";
        $id = Yii::$app->db->createCommand($query)->bindParam(':name', 'Моллюски')->queryScalar();
        
        $this->update('{{%mp_category}}', ['slug' => 'mollyuski'], ['id' => $id]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180215_081529_add_slug_to_salefish_category cannot be reverted.\n";

        return false;
    }

}
