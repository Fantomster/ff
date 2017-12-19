<?php

use yii\db\Migration;

class m170425_100028_add_crockery_category extends Migration {

    public function safeUp() {
        $query = "SELECT id FROM mp_category WHERE name='Сопутствующие товары' AND parent IS NULL";
        $newMasterCategoryId = Yii::$app->db->createCommand($query)->queryScalar();
        if ($newMasterCategoryId) {
            $this->batchInsert('{{%mp_category}}', ['name', 'parent'], [
                ['Посуда', $newMasterCategoryId],
            ]);
        }
    }

    public function safeDown() {
        //
    }

}
