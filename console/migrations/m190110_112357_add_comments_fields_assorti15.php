<?php

use yii\db\Migration;

class m190110_112357_add_comments_fields_assorti15 extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->addCommentOnColumn('{{%waybill}}', 'service_id', 'Идентификатор учётного сервиса (1 - R-Keeper, 2 - IIKO, 8 - 1С, 10 - Tillypad, 11 - Poster)');
    }

    public function safeDown()
    {
        $this->dropCommentFromColumn('{{%waybill}}', 'service_id');
    }
}
