<?php

use yii\db\Migration;

class m181012_075554_add_comments_table_payment_tarif extends Migration
{

    public function safeUp()
    {
        $this->execute('alter table `payment_tarif` comment "Таблица сведений о стоимости платных услуг для организаций";');
        $this->addCommentOnColumn('{{%payment_tarif}}', 'tarif_id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%payment_tarif}}', 'payment_type_id','Идентификатор типа платных услуг');
        $this->addCommentOnColumn('{{%payment_tarif}}', 'organization_type_id','Идентификатор категории организации');
        $this->addCommentOnColumn('{{%payment_tarif}}', 'price','Стоимость услуги');
        $this->addCommentOnColumn('{{%payment_tarif}}', 'status','Статус платной услуги (0 - не активна, 1- активна)');
        $this->addCommentOnColumn('{{%payment_tarif}}', 'organization_id','Идентификатор организации, которой услуга предоставляется');
        $this->addCommentOnColumn('{{%payment_tarif}}', 'individual','Показатель статуса индивидуальности платной услуги (0 - не индивидуальная, 1 - индивидуальная)');
        $this->addCommentOnColumn('{{%payment_tarif}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%payment_tarif}}', 'updated_at','Дата и время последнего изменения записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `payment_tarif` comment "";');
        $this->dropCommentFromColumn('{{%payment_tarif}}', 'tarif_id');
        $this->dropCommentFromColumn('{{%payment_tarif}}', 'payment_type_id');
        $this->dropCommentFromColumn('{{%payment_tarif}}', 'organization_type_id');
        $this->dropCommentFromColumn('{{%payment_tarif}}', 'price');
        $this->dropCommentFromColumn('{{%payment_tarif}}', 'status');
        $this->dropCommentFromColumn('{{%payment_tarif}}', 'organization_id');
        $this->dropCommentFromColumn('{{%payment_tarif}}', 'individual');
        $this->dropCommentFromColumn('{{%payment_tarif}}', 'created_at');
        $this->dropCommentFromColumn('{{%payment_tarif}}', 'updated_at');
    }
}
