<?php

use yii\db\Migration;

class m180725_084759_add_comments_table_rk_waybillstatus extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `rk_waybillstatus` comment "Таблица сведений о статусах выгрузки накладных в системе R-Keeper";');
        $this->addCommentOnColumn('{{%rk_waybillstatus}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%rk_waybillstatus}}', 'denom', 'Наименование статуса выгрузки накладных в системе R-Keeper');
        $this->addCommentOnColumn('{{%rk_waybillstatus}}', 'comment', 'Комментарий (не используется)');
    }

    public function safeDown()
    {
        $this->execute('alter table `rk_waybillstatus` comment "";');
        $this->dropCommentFromColumn('{{%rk_waybillstatus}}', 'id');
        $this->dropCommentFromColumn('{{%rk_waybillstatus}}', 'denom');
        $this->dropCommentFromColumn('{{%rk_waybillstatus}}', 'comment');
    }
}
