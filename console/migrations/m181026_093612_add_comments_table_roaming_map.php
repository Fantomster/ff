<?php

use yii\db\Migration;

class m181026_093612_add_comments_table_roaming_map extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `roaming_map` comment "Таблица сведений о связях покупателей с поставщиками";');
        $this->addCommentOnColumn('{{%roaming_map}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%roaming_map}}', 'acquire_id','Идентификатор организации-покупателя');
        $this->addCommentOnColumn('{{%roaming_map}}', 'acquire_gln_id','Уникальный идентификатор GLN организации-покупателя');
        $this->addCommentOnColumn('{{%roaming_map}}', 'acquire_provider_id','Идентификатор провайдера организации-покупателя');
        $this->addCommentOnColumn('{{%roaming_map}}', 'vendor_id','Идентификатор организации-поставщика');
        $this->addCommentOnColumn('{{%roaming_map}}', 'vendor_gln_id','Уникальный идентификатор GLN организации-поставщика');
        $this->addCommentOnColumn('{{%roaming_map}}', 'vendor_provider_id','Идентификатор провайдера организации-поставщика');
    }

    public function safeDown()
    {
        $this->execute('alter table `roaming_map` comment "";');
        $this->dropCommentFromColumn('{{%roaming_map}}', 'id');
        $this->dropCommentFromColumn('{{%roaming_map}}', 'acquire_id');
        $this->dropCommentFromColumn('{{%roaming_map}}', 'acquire_gln_id');
        $this->dropCommentFromColumn('{{%roaming_map}}', 'acquire_provider_id');
        $this->dropCommentFromColumn('{{%roaming_map}}', 'vendor_id');
        $this->dropCommentFromColumn('{{%roaming_map}}', 'vendor_gln_id');
        $this->dropCommentFromColumn('{{%roaming_map}}', 'vendor_provider_id');
    }
}
