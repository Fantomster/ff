<?php

use yii\db\Migration;

class m180629_084501_add_comments_table_additional_email extends Migration
{

    public function safeUp()
    {
        $this->execute('alter table `additional_email` comment "Таблица сведений о дополнительных е-мэйлах организаций";');
        $this->addCommentOnColumn('{{%additional_email}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%additional_email}}', 'email', 'Е-мэйл');
        $this->addCommentOnColumn('{{%additional_email}}', 'organization_id', 'Идентификатор организации');
        $this->addCommentOnColumn('{{%additional_email}}', 'order_created', 'Показатель состояния необходимости отправлять оповещения о создании заказов');
        $this->addCommentOnColumn('{{%additional_email}}', 'order_canceled', 'Показатель состояния необходимости отправлять оповещения об отмене заказов');
        $this->addCommentOnColumn('{{%additional_email}}', 'order_changed', 'Показатель состояния необходимости отправлять оповещения об изменении заказов');
        $this->addCommentOnColumn('{{%additional_email}}', 'order_processing', 'Показатель состояния необходимости отправлять оповещения о взятии заказов в работу');
        $this->addCommentOnColumn('{{%additional_email}}', 'order_done', 'Показатель состояния необходимости отправлять оповещения о завершении заказов');
        $this->addCommentOnColumn('{{%additional_email}}', 'request_accept', 'Показатель состояния согласия получения оповещений на дополнительный е-мэйл');
    }

    public function safeDown()
    {
        $this->execute('alter table `additional_email` comment "";');
        $this->dropCommentFromColumn('{{%additional_email}}', 'id');
        $this->dropCommentFromColumn('{{%additional_email}}', 'email');
        $this->dropCommentFromColumn('{{%additional_email}}', 'organization_id');
        $this->dropCommentFromColumn('{{%additional_email}}', 'order_created');
        $this->dropCommentFromColumn('{{%additional_email}}', 'order_canceled');
        $this->dropCommentFromColumn('{{%additional_email}}', 'order_changed');
        $this->dropCommentFromColumn('{{%additional_email}}', 'order_processing');
        $this->dropCommentFromColumn('{{%additional_email}}', 'order_done');
        $this->dropCommentFromColumn('{{%additional_email}}', 'request_accept');
    }
}
