<?php

use yii\db\Migration;

class m190201_102625_add_comments_table_egais_write_off_history extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `egais_write_off_history` comment "Таблица сведений о списаниях товаров с указанием идентификатора товара в системе Mixcart";');
        $this->addCommentOnColumn('{{%egais_write_off_history}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%egais_write_off_history}}', 'org_id', 'Идентификатор организации');
        $this->addCommentOnColumn('{{%egais_write_off_history}}', 'act_id', 'Идентификатор акта о списании товаров');
        $this->addCommentOnColumn('{{%egais_write_off_history}}', 'product_id', 'Идентификатор товара в системе Mixcart');
        $this->addCommentOnColumn('{{%egais_write_off_history}}', 'type_write_off_id', 'Идентификатор типа списания товаров');
        $this->addCommentOnColumn('{{%egais_write_off_history}}', 'status', 'Показатель статуса списания товара (0 - не списан, 1 - списан)');
        $this->addCommentOnColumn('{{%egais_write_off_history}}', 'created_at', 'Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%egais_write_off_history}}', 'updated_at', 'Дата и время последнего изменения записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `egais_write_off_history` comment "";');
        $this->dropCommentFromColumn('{{%egais_write_off_history}}', 'id');
        $this->dropCommentFromColumn('{{%egais_write_off_history}}', 'org_id');
        $this->dropCommentFromColumn('{{%egais_write_off_history}}', 'act_id');
        $this->dropCommentFromColumn('{{%egais_write_off_history}}', 'product_id');
        $this->dropCommentFromColumn('{{%egais_write_off_history}}', 'type_write_off_id');
        $this->dropCommentFromColumn('{{%egais_write_off_history}}', 'status');
        $this->dropCommentFromColumn('{{%egais_write_off_history}}', 'created_at');
        $this->dropCommentFromColumn('{{%egais_write_off_history}}', 'updated_at');
    }
}
