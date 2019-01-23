<?php

use yii\db\Migration;

class m190123_090225_add_comments_table_country_vat extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `country_vat` comment "Таблица сведений о ставках налогов в государствах";');
        $this->addCommentOnColumn('{{%country_vat}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%country_vat}}', 'uuid', 'Уникальный идентификатор государства');
        $this->addCommentOnColumn('{{%country_vat}}', 'vats', 'Ставки налогов в процентах');
        $this->addCommentOnColumn('{{%country_vat}}', 'created_at', 'Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%country_vat}}', 'updated_at', 'Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%country_vat}}', 'created_by_id', 'Идентификатор пользователя, создавшего запись');
        $this->addCommentOnColumn('{{%country_vat}}', 'updated_by_id', 'Идентификатор пользователя, последним изменившим запись');
    }

    public function safeDown()
    {
        $this->execute('alter table `country_vat` comment "";');
        $this->dropCommentFromColumn('{{%iiko_waybill}}', 'id');
        $this->dropCommentFromColumn('{{%iiko_waybill}}', 'uuid');
        $this->dropCommentFromColumn('{{%iiko_waybill}}', 'vats');
        $this->dropCommentFromColumn('{{%iiko_waybill}}', 'created_at');
        $this->dropCommentFromColumn('{{%iiko_waybill}}', 'updated_at');
        $this->dropCommentFromColumn('{{%iiko_waybill}}', 'created_by_id');
        $this->dropCommentFromColumn('{{%iiko_waybill}}', 'updated_by_id');
    }
}
