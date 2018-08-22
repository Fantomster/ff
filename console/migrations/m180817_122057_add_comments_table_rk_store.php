<?php

use yii\db\Migration;

class m180817_122057_add_comments_table_rk_store extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `rk_store` comment "Таблица сведений о складах в системе R-Keeper";');
        $this->addCommentOnColumn('{{%rk_store}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%rk_store}}', 'acc','Идентификатор организации, к которой относится склад');
        $this->addCommentOnColumn('{{%rk_store}}', 'rid','Уникальный системный идентификатор склада в системе R-Keeper');
        $this->addCommentOnColumn('{{%rk_store}}', 'denom','Наименование склада в системе R-Keeper');
        $this->addCommentOnColumn('{{%rk_store}}', 'store_type','Тип склада в системе R-Keeper');
        $this->addCommentOnColumn('{{%rk_store}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%rk_store}}', 'updated_at','Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%rk_store}}', 'comment','Комментарий');
        $this->addCommentOnColumn('{{%rk_store}}', 'is_active','Показатель состояния активности склада в системе R-Keeper');
    }

    public function safeDown()
    {
        $this->execute('alter table `rk_store` comment "";');
        $this->dropCommentFromColumn('{{%rk_store}}', 'id');
        $this->dropCommentFromColumn('{{%rk_store}}', 'acc');
        $this->dropCommentFromColumn('{{%rk_store}}', 'rid');
        $this->dropCommentFromColumn('{{%rk_store}}', 'denom');
        $this->dropCommentFromColumn('{{%rk_store}}', 'store_type');
        $this->dropCommentFromColumn('{{%rk_store}}', 'created_at');
        $this->dropCommentFromColumn('{{%rk_store}}', 'updated_at');
        $this->dropCommentFromColumn('{{%rk_store}}', 'comment');
        $this->dropCommentFromColumn('{{%rk_store}}', 'is_active');
    }
}
