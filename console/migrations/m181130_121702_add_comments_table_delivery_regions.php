<?php

use yii\db\Migration;

class m181130_121702_add_comments_table_delivery_regions extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `delivery_regions` comment "Таблица сведений о зонах доставки товаров поставщиками";');
        $this->addCommentOnColumn('{{%delivery_regions}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%delivery_regions}}', 'supplier_id','Идентификатор организации-поставщика, осуществляющей доставку товаров');
        $this->addCommentOnColumn('{{%delivery_regions}}', 'country','Государство, на территорию которого осуществляется доставка');
        $this->addCommentOnColumn('{{%delivery_regions}}', 'locality','Населённый пункт, в который осуществляется доставка');
        $this->addCommentOnColumn('{{%delivery_regions}}', 'administrative_area_level_1','Административный регион (1-й уровень), на территорию которого осуществляется доставка');
        $this->addCommentOnColumn('{{%delivery_regions}}', 'exception','Показатель состояния одобрения показа товаров поставщика в Market (0 - не одобрено, 1 - одобрено)');
        $this->addCommentOnColumn('{{%delivery_regions}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%delivery_regions}}', 'updated_at','Дата и время последнего изменения записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `delivery_regions` comment "";');
        $this->dropCommentFromColumn('{{%delivery_regions}}', 'id');
        $this->dropCommentFromColumn('{{%delivery_regions}}', 'supplier_id');
        $this->dropCommentFromColumn('{{%delivery_regions}}', 'country');
        $this->dropCommentFromColumn('{{%delivery_regions}}', 'locality');
        $this->dropCommentFromColumn('{{%delivery_regions}}', 'administrative_area_level_1');
        $this->dropCommentFromColumn('{{%delivery_regions}}', 'exception');
        $this->dropCommentFromColumn('{{%delivery_regions}}', 'created_at');
        $this->dropCommentFromColumn('{{%delivery_regions}}', 'updated_at');
    }
}
