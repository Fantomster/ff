<?php

use yii\db\Migration;

class m180831_074626_add_comments_table_one_s_waybill_status extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `one_s_waybill_status` comment "Таблица сведений о статусах выгрузки накладных в системе 1С";');
        $this->addCommentOnColumn('{{%one_s_waybill_status}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%one_s_waybill_status}}', 'denom', 'Наименование статуса выгрузки накладных в системе 1С');
        $this->addCommentOnColumn('{{%one_s_waybill_status}}', 'comment', 'Комментарий (не используется)');
    }

    public function safeDown()
    {
        $this->execute('alter table `one_s_waybill_status` comment "";');
        $this->dropCommentFromColumn('{{%one_s_waybill_status}}', 'id');
        $this->dropCommentFromColumn('{{%one_s_waybill_status}}', 'denom');
        $this->dropCommentFromColumn('{{%one_s_waybill_status}}', 'comment');
    }
}
