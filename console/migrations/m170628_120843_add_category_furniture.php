<?php

use yii\db\Migration;

class m170628_120843_add_category_furniture extends Migration
{
    public function safeUp()
    {
        $newMasterCategory = new \common\models\MpCategory();
        $newMasterCategory->name = 'Мебель';
        $newMasterCategory->parent = null;
        $newMasterCategory->save();
        $newMasterCategory->refresh();
        $this->batchInsert('{{%mp_category}}', ['name','parent'], [
            ['Столы', $newMasterCategory->id],
            ['Стулья', $newMasterCategory->id],
            ['Мягкая мебель', $newMasterCategory->id],
            ['Барные стойки, станции официанта, ресепшн', $newMasterCategory->id],
            ['Двери', $newMasterCategory->id],
            ['Реставрация', $newMasterCategory->id],
            ['Интерьер', $newMasterCategory->id],
            ['Производство мебели на заказ', $newMasterCategory->id]
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
