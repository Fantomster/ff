<?php

use yii\db\Migration;

class m181012_075955_add_comments_table_payment extends Migration
{

    public function safeUp()
    {
        $this->execute('alter table `payment` comment "Таблица сведений об оплатах услуг организациями";');
        $this->addCommentOnColumn('{{%payment}}', 'payment_id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%payment}}', 'total','Сумма оплаты');
        $this->addCommentOnColumn('{{%payment}}', 'receipt_number','Номер выставленного счёта');
        $this->addCommentOnColumn('{{%payment}}', 'organization_id','Идентификатор организации, которой выставлен счёт за оплату услуги');
        $this->addCommentOnColumn('{{%payment}}', 'type_payment','Идентификатор типа платной услуги');
        $this->addCommentOnColumn('{{%payment}}', 'email','Е-мэйл организации, оплачивающей услугу');
        $this->addCommentOnColumn('{{%payment}}', 'phone','Номер телефона организации, оплачивающей платную услугу');
        $this->addCommentOnColumn('{{%payment}}', 'date','Дата оплаты');
        $this->addCommentOnColumn('{{%payment}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%payment}}', 'updated_at','Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%payment}}', 'status','Показатель статуса счёта за услугу (0 - ошибочный, 1 - выставленный, 2 - оплаченный)');
    }

    public function safeDown()
    {
        $this->execute('alter table `payment` comment "";');
        $this->dropCommentFromColumn('{{%payment}}', 'payment_id');
        $this->dropCommentFromColumn('{{%payment}}', 'total');
        $this->dropCommentFromColumn('{{%payment}}', 'receipt_number');
        $this->dropCommentFromColumn('{{%payment}}', 'organization_id');
        $this->dropCommentFromColumn('{{%payment}}', 'type_payment');
        $this->dropCommentFromColumn('{{%payment}}', 'email');
        $this->dropCommentFromColumn('{{%payment}}', 'phone');
        $this->dropCommentFromColumn('{{%payment}}', 'date');
        $this->dropCommentFromColumn('{{%payment}}', 'created_at');
        $this->dropCommentFromColumn('{{%payment}}', 'updated_at');
        $this->dropCommentFromColumn('{{%payment}}', 'status');
    }
}
