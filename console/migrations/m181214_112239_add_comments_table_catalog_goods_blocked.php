<?php

use yii\db\Migration;

class m181214_112239_add_comments_table_catalog_goods_blocked extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `catalog_goods_blocked` comment "Таблица сведений о товарах каталогов, не подлежащих заказам";');
        $this->addCommentOnColumn('{{%catalog_goods_blocked}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%catalog_goods_blocked}}', 'cbg_id','Идентификатор товара в таблице catalog_base_goods');
        $this->addCommentOnColumn('{{%catalog_goods_blocked}}', 'owner_organization_id','Идентификатор организации-ресторана, указавшей товар как не подходящий для заказа');
        $this->addCommentOnColumn('{{%catalog_goods_blocked}}', 'created_at','Дата и время создания записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `catalog_goods_blocked` comment "";');
        $this->dropCommentFromColumn('{{%catalog_goods_blocked}}', 'id');
        $this->dropCommentFromColumn('{{%catalog_goods_blocked}}', 'cbg_id');
        $this->dropCommentFromColumn('{{%catalog_goods_blocked}}', 'owner_organization_id');
        $this->dropCommentFromColumn('{{%catalog_goods_blocked}}', 'created_at');
    }
}
