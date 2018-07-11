<?php

use yii\db\Migration;

class m180711_090108_edit_comment_table_role extends Migration
{
    public function safeUp()
    {
        $this->addCommentOnColumn('{{%role}}', 'organization_type', 'Идентификатор типа организации (1 - ресторан, 2 - поставщик, 3 - франчайзинг)');
    }

    public function safeDown()
    {
        $this->dropCommentFromColumn('{{%role}}', 'organization_type');
    }

}
