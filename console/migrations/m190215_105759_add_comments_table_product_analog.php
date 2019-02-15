<?php

use yii\db\Migration;

class m190215_105759_add_comments_table_product_analog extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `product_analog` comment "Таблица сведений об аналогах товаров";');
        $this->addCommentOnColumn('{{%product_analog}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%product_analog}}', 'client_id','Идентификатор организации-ресторана');
        $this->addCommentOnColumn('{{%product_analog}}', 'product_id','Идентификатор товара в таблице catalog_base_goods');
        $this->addCommentOnColumn('{{%product_analog}}', 'parent_id','Идентификатор главного аналога в таблице product_analog (если товар сам является главным аналогом, то значение null)');
        $this->addCommentOnColumn('{{%product_analog}}', 'sort_value','Поле, содержашее произвольное значение для сортировки');
        $this->addCommentOnColumn('{{%product_analog}}', 'coefficient','Коэффициент пересчёта количества товара по сравнению с главным аналогом (у главного аналога коэффициент равен 1)');
    }

    public function safeDown()
    {
        $this->execute('alter table `product_analog` comment "";');
        $this->dropCommentFromColumn('{{%product_analog}}', 'id');
        $this->dropCommentFromColumn('{{%product_analog}}', 'client_id');
        $this->dropCommentFromColumn('{{%product_analog}}', 'product_id');
        $this->dropCommentFromColumn('{{%product_analog}}', 'parent_id');
        $this->dropCommentFromColumn('{{%product_analog}}', 'sort_value');
        $this->dropCommentFromColumn('{{%product_analog}}', 'coefficient');
    }
}
