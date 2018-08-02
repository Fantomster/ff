<?php

use yii\db\Migration;

class m180731_102806_add_comments_table_order_content extends Migration
{

    public function safeUp()
    {
        $this->execute('alter table `order_content` comment "Таблица сведений о товарных позициях заказов";');
        $this->addCommentOnColumn('{{%order_content}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%order_content}}', 'order_id','Идентификатор заказа, к которому относится товарная позиция');
        $this->addCommentOnColumn('{{%order_content}}', 'product_id','Идентификатор товара в таблице product');
        $this->addCommentOnColumn('{{%order_content}}', 'quantity','Количество товара');
        $this->addCommentOnColumn('{{%order_content}}', 'price','Цена товара');
        $this->addCommentOnColumn('{{%order_content}}', 'initial_quantity','Первоначальное количество товара');
        $this->addCommentOnColumn('{{%order_content}}', 'product_name','Наименование товарной позиции');
        $this->addCommentOnColumn('{{%order_content}}', 'units','Единица измерения товара');
        $this->addCommentOnColumn('{{%order_content}}', 'article','Артикул товара из накладной ТОРГ-12');
        $this->addCommentOnColumn('{{%order_content}}', 'comment','Комментарий (не используется)');
        $this->addCommentOnColumn('{{%order_content}}', 'plan_price','Изменённая цена товара');
        $this->addCommentOnColumn('{{%order_content}}', 'plan_quantity','Изменённое количество товара');
        $this->addCommentOnColumn('{{%order_content}}', 'updated_at','Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%order_content}}', 'updated_user_id','Идентификатор пользователя, совершившего последние изменения записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `order_content` comment "";');
        $this->dropCommentFromColumn('{{%order_content}}', 'id');
        $this->dropCommentFromColumn('{{%order_content}}', 'order_id');
        $this->dropCommentFromColumn('{{%order_content}}', 'product_id');
        $this->dropCommentFromColumn('{{%order_content}}', 'quantity');
        $this->dropCommentFromColumn('{{%order_content}}', 'price');
        $this->dropCommentFromColumn('{{%order_content}}', 'initial_quantity');
        $this->dropCommentFromColumn('{{%order_content}}', 'product_name');
        $this->dropCommentFromColumn('{{%order_content}}', 'units');
        $this->dropCommentFromColumn('{{%order_content}}', 'article');
        $this->dropCommentFromColumn('{{%order_content}}', 'comment');
        $this->dropCommentFromColumn('{{%order_content}}', 'plan_price');
        $this->dropCommentFromColumn('{{%order_content}}', 'plan_quantity');
        $this->dropCommentFromColumn('{{%order_content}}', 'updated_at');
        $this->dropCommentFromColumn('{{%order_content}}', 'updated_user_id');
    }
}
