<?php

use yii\db\Migration;

class m170411_115854_add_new_categories extends Migration
{
    public function safeUp()
    {
        $this->batchInsert('{{%mp_category}}', ['name','parent'], [
            ['Продукты глубокой заморозки', null],
        ]);
        $query = "SELECT id FROM mp_category WHERE name='Продукты глубокой заморозки' AND parent IS NULL";
        $newMasterCategoryId = Yii::$app->db->createCommand($query)->queryScalar();
        $this->batchInsert('{{%mp_category}}', ['name','parent'], [
            ['Пельмени, Хинкали, Вареники', $newMasterCategoryId],
            ['Овощи, фрукты и ягоды', $newMasterCategoryId],
            ['Картофель', $newMasterCategoryId],
            ['Блины', $newMasterCategoryId],
            ['Пицца и тесто', $newMasterCategoryId],
            ['Готовые блюда', $newMasterCategoryId],
            ['Котлеты и наггетсы', $newMasterCategoryId],
            ['Морепродукты', $newMasterCategoryId],
            ['Мясо и птица', $newMasterCategoryId],
        ]);
    }

    public function safeDown()
    {
    }
}
