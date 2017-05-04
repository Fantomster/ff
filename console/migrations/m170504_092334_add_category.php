<?php

use yii\db\Migration;

class m170504_092334_add_category extends Migration
{
    public function safeUp() {
        $masterCategory = common\models\MpCategory::findOne(['name' => 'Сопутствующие товары']);
        if ($masterCategory) {
            $this->batchInsert('{{%mp_category}}', ['name', 'parent'], [
                ['Канцтовары', $masterCategory->id],
            ]);
        }
    }

    public function safeDown() {
        //
    }
}
