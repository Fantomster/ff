<?php

use yii\db\Migration;

class m180726_124615_add_comments_table_iiko_waybillstatus extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `iiko_waybill_status` comment "Таблица сведений о статусах выгрузки накладных в системе IIKO";');
        $this->addCommentOnColumn('{{%iiko_waybill_status}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%iiko_waybill_status}}', 'denom', 'Наименование статуса выгрузки накладных в системе IIKO');
        $this->addCommentOnColumn('{{%iiko_waybill_status}}', 'comment', 'Комментарий (не используется)');
    }

    public function safeDown()
    {
        $this->execute('alter table `iiko_waybill_status` comment "";');
        $this->dropCommentFromColumn('{{%iiko_waybill_status}}', 'id');
        $this->dropCommentFromColumn('{{%iiko_waybill_status}}', 'denom');
        $this->dropCommentFromColumn('{{%iiko_waybill_status}}', 'comment');
    }
}
