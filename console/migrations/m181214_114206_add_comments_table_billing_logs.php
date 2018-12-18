<?php

use yii\db\Migration;

class m181214_114206_add_comments_table_billing_logs extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `billing_logs` comment "Таблица сведений о логах платежей";');
        $this->addCommentOnColumn('{{%billing_logs}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%billing_logs}}', 'message','Сообщение о ситуации при проведении платежа');
        $this->addCommentOnColumn('{{%billing_logs}}', 'date','Дата и время проведения платежа');
        $this->addCommentOnColumn('{{%billing_logs}}', 'url','URL-адрес при проведении платежа');
        $this->addCommentOnColumn('{{%billing_logs}}', 'method','Наименование метода, задействованного при проведении платежа');
        $this->addCommentOnColumn('{{%billing_logs}}', 'headers','Заголовки запроса при проведении платежа');
        $this->addCommentOnColumn('{{%billing_logs}}', 'ip','IP-адрес пользователя, проводившего платёж');
        $this->addCommentOnColumn('{{%billing_logs}}', 'action','Наименование действия контроллера, после которого записывается данная запись в лог');
        $this->addCommentOnColumn('{{%billing_logs}}', 'status','Показатель статуса действия при проведении платежа (успешно, ошибка, ошибка API)');
    }

    public function safeDown()
    {
        $this->execute('alter table `billing_logs` comment "";');
        $this->dropCommentFromColumn('{{%billing_logs}}', 'id');
        $this->dropCommentFromColumn('{{%billing_logs}}', 'message');
        $this->dropCommentFromColumn('{{%billing_logs}}', 'date');
        $this->dropCommentFromColumn('{{%billing_logs}}', 'url');
        $this->dropCommentFromColumn('{{%billing_logs}}', 'method');
        $this->dropCommentFromColumn('{{%billing_logs}}', 'headers');
        $this->dropCommentFromColumn('{{%billing_logs}}', 'ip');
        $this->dropCommentFromColumn('{{%billing_logs}}', 'action');
        $this->dropCommentFromColumn('{{%billing_logs}}', 'status');
    }
}
