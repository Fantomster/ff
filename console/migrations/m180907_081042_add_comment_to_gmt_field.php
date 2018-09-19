<?php

use yii\db\Migration;

/**
 * Class m180907_081042_add_comment_to_gmt_field
 */
class m180907_081042_add_comment_to_gmt_field extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addCommentOnColumn("{{%organization}}", 'gmt', "Временная зона по Гринвичу GMT");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addCommentOnColumn("{{%organization}}", 'gmt');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180907_081042_add_comment_to_gmt_field cannot be reverted.\n";

        return false;
    }
    */
}
