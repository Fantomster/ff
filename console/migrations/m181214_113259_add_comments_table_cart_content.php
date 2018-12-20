<?php

use yii\db\Migration;

class m181214_113259_add_comments_table_cart_content extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `cart_content` comment "Таблица сведений о товарах, отложенных в корзины";');
        $this->addCommentOnColumn('{{%cart_content}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%cart_content}}', 'cart_id','Идентификатор корзины');
        $this->addCommentOnColumn('{{%cart_content}}', 'vendor_id','Идентификатор организации-поставщика данного товара');
        $this->addCommentOnColumn('{{%cart_content}}', 'product_id','Идентификатор товара, отложенного в корзину');
        $this->addCommentOnColumn('{{%cart_content}}', 'product_name','Наименование товара, отложенного в корзину');
        $this->addCommentOnColumn('{{%cart_content}}', 'quantity','Количество данного товара, отложенного в корзину');
        $this->addCommentOnColumn('{{%cart_content}}', 'price','Цена данного товара');
        $this->addCommentOnColumn('{{%cart_content}}', 'units','Единица измерения данного товара');
        $this->addCommentOnColumn('{{%cart_content}}', 'comment','Комментарий сотрудника ресторана к данному товару');
        $this->addCommentOnColumn('{{%cart_content}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%cart_content}}', 'updated_at','Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%cart_content}}', 'currency_id','Идентификатор валюты');
    }

    public function safeDown()
    {
        $this->execute('alter table `cart_content` comment "";');
        $this->dropCommentFromColumn('{{%cart_content}}', 'id');
        $this->dropCommentFromColumn('{{%cart_content}}', 'cart_id');
        $this->dropCommentFromColumn('{{%cart_content}}', 'vendor_id');
        $this->dropCommentFromColumn('{{%cart_content}}', 'product_id');
        $this->dropCommentFromColumn('{{%cart_content}}', 'product_name');
        $this->dropCommentFromColumn('{{%cart_content}}', 'quantity');
        $this->dropCommentFromColumn('{{%cart_content}}', 'price');
        $this->dropCommentFromColumn('{{%cart_content}}', 'units');
        $this->dropCommentFromColumn('{{%cart_content}}', 'comment');
        $this->dropCommentFromColumn('{{%cart_content}}', 'created_at');
        $this->dropCommentFromColumn('{{%cart_content}}', 'updated_at');
        $this->dropCommentFromColumn('{{%cart_content}}', 'currency_id');
    }
}
