<?php

use yii\db\Migration;

class m170628_120843_add_category_furniture extends Migration
{
    public function safeUp()
    {
        $name = 'Мебель';
        $parent = null;

        $this->insert('{{%mp_category}}', [
            'name' => $name,
            'parent' => $parent
        ]);

        $query = "SELECT id FROM mp_category WHERE name = :name AND parent IS NULL";
        $parent_id = Yii::$app->db->createCommand($query)->bindParam(':name', $name)->queryScalar();

        if (empty($parent_id)) {
            throw new \Exception('parent_id not found');
        }

        $this->batchInsert('{{%mp_category}}', ['name', 'parent'], [
            ['Столы', $parent_id],
            ['Стулья', $parent_id],
            ['Мягкая мебель', $parent_id],
            ['Барные стойки, станции официанта, ресепшн', $parent_id],
            ['Двери', $parent_id],
            ['Реставрация', $parent_id],
            ['Интерьер', $parent_id],
            ['Производство мебели на заказ', $parent_id]
        ]);
    }

    public function safeDown()
    {
        echo "m170628_120843_add_category_furniture cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m170628_120843_add_category_furniture cannot be reverted.\n";

        return false;
    }
    */
}
