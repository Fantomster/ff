<?php

use yii\db\Migration;

class m181214_113957_add_comments_table_billing_payment extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `billing_payment` comment "Таблица сведений о платежах";');
        $this->addCommentOnColumn('{{%billing_payment}}', 'billing_payment_id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%billing_payment}}', 'amount','Сумма платежа');
        $this->addCommentOnColumn('{{%billing_payment}}', 'currency_id','Идентификатор валюты');
        $this->addCommentOnColumn('{{%billing_payment}}', 'user_id','Идентификатор пользователя');
        $this->addCommentOnColumn('{{%billing_payment}}', 'organization_id','Идентификатор организации');
        $this->addCommentOnColumn('{{%billing_payment}}', 'status','Показатель статуса платежа (0 - новый, 1 - в ожидании выполнения, 2 - успешно выполнен, 9 - отменён)');
        $this->addCommentOnColumn('{{%billing_payment}}', 'payment_type_id','Идентификатор типа платежа');
        $this->addCommentOnColumn('{{%billing_payment}}', 'idempotency_key','Ключ идемпотенции');
        $this->addCommentOnColumn('{{%billing_payment}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%billing_payment}}', 'capture_at','Дата и время подтверждения платежа');
        $this->addCommentOnColumn('{{%billing_payment}}', 'payment_at','Дата и время оплаты платежа');
        $this->addCommentOnColumn('{{%billing_payment}}', 'refund_at','Дата и время отмены платежа');
        $this->addCommentOnColumn('{{%billing_payment}}', 'external_payment_id','Ключ платежа в платёжной системе');
        $this->addCommentOnColumn('{{%billing_payment}}', 'external_created_at','Дата и время создания платежа у провайдера');
        $this->addCommentOnColumn('{{%billing_payment}}', 'external_expires_at','Дата и время крайнего срока для подтверждения платежа');
        $this->addCommentOnColumn('{{%billing_payment}}', 'provider','Наименование провайдера');
    }

    public function safeDown()
    {
        $this->execute('alter table `billing_payment` comment "";');
        $this->dropCommentFromColumn('{{%billing_payment}}', 'billing_payment_id');
        $this->dropCommentFromColumn('{{%billing_payment}}', 'amount');
        $this->dropCommentFromColumn('{{%billing_payment}}', 'currency_id');
        $this->dropCommentFromColumn('{{%billing_payment}}', 'user_id');
        $this->dropCommentFromColumn('{{%billing_payment}}', 'organization_id');
        $this->dropCommentFromColumn('{{%billing_payment}}', 'status');
        $this->dropCommentFromColumn('{{%billing_payment}}', 'payment_type_id');
        $this->dropCommentFromColumn('{{%billing_payment}}', 'idempotency_key');
        $this->dropCommentFromColumn('{{%billing_payment}}', 'created_at');
        $this->dropCommentFromColumn('{{%billing_payment}}', 'capture_at');
        $this->dropCommentFromColumn('{{%billing_payment}}', 'payment_at');
        $this->dropCommentFromColumn('{{%billing_payment}}', 'refund_at');
        $this->dropCommentFromColumn('{{%billing_payment}}', 'external_payment_id');
        $this->dropCommentFromColumn('{{%billing_payment}}', 'external_created_at');
        $this->dropCommentFromColumn('{{%billing_payment}}', 'external_expires_at');
        $this->dropCommentFromColumn('{{%billing_payment}}', 'provider');
    }
}
