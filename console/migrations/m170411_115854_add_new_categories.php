<?php

use yii\db\Migration;

class m170411_115854_add_new_categories extends Migration
{
    public function safeUp()
    {
        $newMasterCategory = new \common\models\MpCategory();
        $newMasterCategory->name = 'Продукты глубокой заморозки';
        $newMasterCategory->parent = null;
        $newMasterCategory->save();
        $newMasterCategory->refresh();
        $this->batchInsert('{{%mp_category}}', ['name','parent'], [
            ['Пельмени, Хинкали, Вареники', $newMasterCategory->id],
            ['Овощи, фрукты и ягоды', $newMasterCategory->id],
            ['Картофель', $newMasterCategory->id],
            ['Блины', $newMasterCategory->id],
            ['Пицца и тесто', $newMasterCategory->id],
            ['Готовые блюда', $newMasterCategory->id],
            ['Котлеты и наггетсы', $newMasterCategory->id],
            ['Морепродукты', $newMasterCategory->id],
            ['Мясо и птица', $newMasterCategory->id],
        ]);
    }

    public function safeDown()
    {
    }
}
