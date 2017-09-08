<?php

use yii\db\Migration;

class m170901_144550_category_slug extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%mp_category}}', 'slug', $this->string()->null());
    }

    public function safeDown()
    {
        $this->dropColumn('{{%mp_category}}', 'slug');
    }
}
