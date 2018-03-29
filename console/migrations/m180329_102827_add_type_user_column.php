<?php

use yii\db\Migration;

/**
 * Class m180329_102827_add_type_user_column
 */
class m180329_102827_add_type_user_column extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'type', $this->integer()->defaultValue(0));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%user}}', 'type');
    }
}
