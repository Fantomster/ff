<?php

use yii\db\Migration;

class m170320_101028_update_category_3 extends Migration
{
    public function safeUp()
    {
        $this->delete('{{%mp_category}}', ['id' => 213]);
        $this->delete('{{%mp_category}}', ['id' => 214]);
        $this->delete('{{%mp_category}}', ['id' => 215]);
        $this->delete('{{%mp_category}}', ['id' => 216]);
        $this->batchInsert('{{%mp_category}}', ['name','parent'], [
            ['Весовое',212],
            ['Порционное',212],
        ]);
    }
}
