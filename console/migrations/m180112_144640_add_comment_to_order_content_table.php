<?php

use yii\db\Migration;

/**
 * Class m180112_144640_add_comment_to_order_content_table
 */
class m180112_144640_add_comment_to_order_content_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%order_content}}', 'comment', $this->string(255)->null()->defaultValue(null));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%order_content}}', 'comment');
    }
}
