<?php

use yii\db\Migration;

/**
 * Class m171226_111643_alter_is_invited
 */
class m171226_111643_alter_is_invited extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%organization}}', 'is_invited', $this->boolean()->notNull()->defaultValue(false));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%organization}}', 'is_invited');
    }
}
