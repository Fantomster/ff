<?php

use yii\db\Migration;

class m181219_065506_add_comments_fields_service_id extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addCommentOnColumn('{{%catalog_goods}}', 'service_id', 'Указатель на ID сервиса');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropCommentFromColumn('{{%catalog_goods}}', 'service_id');
    }
}
