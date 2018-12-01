<?php

use yii\db\Migration;

class m181130_121852_add_comments_table_delivery extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `delivery` comment "Таблица сведений об условиях доставки товаров от поставщиков";');
        $this->addCommentOnColumn('{{%delivery}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%delivery}}', 'vendor_id','Идентификатор организации-поставщика');
        $this->addCommentOnColumn('{{%delivery}}', 'delivery_charge','Стоимость доставки');
        $this->addCommentOnColumn('{{%delivery}}', 'min_free_delivery_charge','Минимальная стоимость заказа, при которой доставка осуществляется бесплатно');
        $this->addCommentOnColumn('{{%delivery}}', 'mon','Показатель возможности доставки заказа в понедельник (0 - нет доставки, 1 - доставка возможна)');
        $this->addCommentOnColumn('{{%delivery}}', 'tue','Показатель возможности доставки заказа во вторник (0 - нет доставки, 1 - доставка возможна)');
        $this->addCommentOnColumn('{{%delivery}}', 'wed','Показатель возможности доставки заказа в среду (0 - нет доставки, 1 - доставка возможна)');
        $this->addCommentOnColumn('{{%delivery}}', 'thu','Показатель возможности доставки заказа в четверг (0 - нет доставки, 1 - доставка возможна)');
        $this->addCommentOnColumn('{{%delivery}}', 'fri','Показатель возможности доставки заказа в пятницу (0 - нет доставки, 1 - доставка возможна)');
        $this->addCommentOnColumn('{{%delivery}}', 'sat','Показатель возможности доставки заказа в субботу (0 - нет доставки, 1 - доставка возможна)');
        $this->addCommentOnColumn('{{%delivery}}', 'sun','Показатель возможности доставки заказа в воскресенье (0 - нет доставки, 1 - доставка возможна)');
        $this->addCommentOnColumn('{{%delivery}}', 'min_order_price','Минимальная стоимость заказа');
        $this->addCommentOnColumn('{{%delivery}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%delivery}}', 'updated_at','Дата и время последнего изменения записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `delivery` comment "";');
        $this->dropCommentFromColumn('{{%delivery}}', 'id');
        $this->dropCommentFromColumn('{{%delivery}}', 'vendor_id');
        $this->dropCommentFromColumn('{{%delivery}}', 'delivery_charge');
        $this->dropCommentFromColumn('{{%delivery}}', 'min_free_delivery_charge');
        $this->dropCommentFromColumn('{{%delivery}}', 'mon');
        $this->dropCommentFromColumn('{{%delivery}}', 'tue');
        $this->dropCommentFromColumn('{{%delivery}}', 'wed');
        $this->dropCommentFromColumn('{{%delivery}}', 'thu');
        $this->dropCommentFromColumn('{{%delivery}}', 'fri');
        $this->dropCommentFromColumn('{{%delivery}}', 'sat');
        $this->dropCommentFromColumn('{{%delivery}}', 'sun');
        $this->dropCommentFromColumn('{{%delivery}}', 'min_order_price');
        $this->dropCommentFromColumn('{{%delivery}}', 'created_at');
        $this->dropCommentFromColumn('{{%delivery}}', 'updated_at');
    }
}
