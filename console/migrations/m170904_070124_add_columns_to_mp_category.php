<?php

use yii\db\Migration;

class m170904_070124_add_columns_to_mp_category extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%mp_category}}', 'title', $this->string()->null());
        $this->addColumn('{{%mp_category}}', 'text', $this->text()->null());
        $this->addColumn('{{%mp_category}}', 'description', $this->text()->null());
        $this->addColumn('{{%mp_category}}', 'keywords', $this->text()->null());
    }

    public function safeDown()
    {
        $this->dropColumn('{{%mp_category}}', 'title');
        $this->dropColumn('{{%mp_category}}', 'text');
        $this->dropColumn('{{%mp_category}}', 'description');
        $this->dropColumn('{{%mp_category}}', 'keywords');
    }
}
