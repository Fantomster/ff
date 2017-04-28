<?php

use yii\db\Migration;

class m170425_100028_add_crockery_category extends Migration {

    public function safeUp() {
        $masterCategory = common\models\MpCategory::findOne(['name' => 'Сопутствующие товары']);
        if ($masterCategory) {
            $this->batchInsert('{{%mp_category}}', ['name', 'parent'], [
                ['Посуда', $masterCategory->id],
            ]);
        }
    }

    public function safeDown() {
        //
    }

}
