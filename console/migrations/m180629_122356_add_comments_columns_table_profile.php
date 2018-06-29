<?php

use yii\db\Migration;

class m180629_122356_add_comments_columns_table_profile extends Migration
{

    public function safeUp()
    {
        $this->addCommentOnColumn('{{%profile}}', 'job_id', 'Идентификатор должности пользователя');
        $this->addCommentOnColumn('{{%profile}}', 'gender', 'Идентификатор гендерного пола пользователя');
    }

    public function safeDown()
    {
        $this->dropCommentFromColumn('{{%profile}}', 'job_id');
        $this->dropCommentFromColumn('{{%profile}}', 'gender');
    }
}
