<?php

use yii\db\Migration;

class m181130_122911_add_comments_fields_assorti11 extends Migration
{
    public function safeUp()
    {
        $this->addCommentOnColumn('{{%organization}}', 'vendor_is_work', 'Показатель, что организация-поставщик работает с системой (0 - не работает, 1 - работает)');
    }

    public function safeDown()
    {
        $this->dropCommentFromColumn('{{%organization}}', 'vendor_is_work');
    }
}
