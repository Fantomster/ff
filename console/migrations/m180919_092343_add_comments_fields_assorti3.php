<?php

use yii\db\Migration;

class m180919_092343_add_comments_fields_assorti3 extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->addCommentOnColumn('{{%iiko_waybill}}', 'autostatus_id', 'Идентификатор статуса автоматической выгрузки накладной (0 - отклонена, 1 - выгружена автоматически, 2 - выгружена вручную, 3 - сформирована)');
        $this->addCommentOnColumn('{{%rk_waybill}}', 'autostatus_id', 'Идентификатор статуса автоматической выгрузки накладной (0 - отклонена, 1 - выгружена автоматически, 2 - выгружена вручную, 3 - сформирована)');
        $this->addCommentOnColumn('{{%one_s_waybill}}', 'autostatus_id', 'Идентификатор статуса автоматической выгрузки накладной (0 - отклонена, 1 - выгружена автоматически, 2 - выгружена вручную, 3 - сформирована)');
    }

    public function safeDown()
    {
        $this->dropCommentFromColumn('{{%iiko_waybill}}', 'autostatus_id');
        $this->dropCommentFromColumn('{{%rk_waybill}}', 'autostatus_id');
        $this->dropCommentFromColumn('{{%one_s_waybill}}', 'autostatus_id');
    }
}
