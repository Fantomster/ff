<?php

use yii\db\Migration;

class m180831_075216_add_comments_table_all_map extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `all_map` comment "Таблица сведений о массовом сопоставлении товаров";');
        $this->addCommentOnColumn('{{%all_map}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%all_map}}', 'service_id','Идентификатор сервиса интеграции (all_service)');
        $this->addCommentOnColumn('{{%all_map}}', 'org_id','Идентификатор организации-ресторана');
        $this->addCommentOnColumn('{{%all_map}}', 'product_id','Идентификатор продукта поставщика');
        $this->addCommentOnColumn('{{%all_map}}', 'supp_id','Идентификатор организации-поставщика');
        $this->addCommentOnColumn('{{%all_map}}', 'serviceproduct_id','Идентификатор продукта в учётной системе расторана');
        $this->addCommentOnColumn('{{%all_map}}', 'unit_rid','Идентификатор единицы измерения в учётной системе ресторана');
        $this->addCommentOnColumn('{{%all_map}}', 'store_rid','Идентификатор склада в учётной системе ресторана');
        $this->addCommentOnColumn('{{%all_map}}', 'koef','Коэффициент пересчёт единиц измерения');
        $this->addCommentOnColumn('{{%all_map}}', 'vat','Ставка налога НДС данного продукта');
        $this->addCommentOnColumn('{{%all_map}}', 'is_active','Показатель состояния активности (0 - не активно, 1 - активно)');
        $this->addCommentOnColumn('{{%all_map}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%all_map}}', 'linked_at','Дата и время сопоставления товаров');
        $this->addCommentOnColumn('{{%all_map}}', 'updated_at','Дата и время последнего изменения записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `all_map` comment "";');
        $this->dropCommentFromColumn('{{%all_map}}', 'id');
        $this->dropCommentFromColumn('{{%all_map}}', 'service_id');
        $this->dropCommentFromColumn('{{%all_map}}', 'org_id');
        $this->dropCommentFromColumn('{{%all_map}}', 'product_id');
        $this->dropCommentFromColumn('{{%all_map}}', 'supp_id');
        $this->dropCommentFromColumn('{{%all_map}}', 'serviceproduct_id');
        $this->dropCommentFromColumn('{{%all_map}}', 'unit_rid');
        $this->dropCommentFromColumn('{{%all_map}}', 'store_rid');
        $this->dropCommentFromColumn('{{%all_map}}', 'koef');
        $this->dropCommentFromColumn('{{%all_map}}', 'vat');
        $this->dropCommentFromColumn('{{%all_map}}', 'is_active');
        $this->dropCommentFromColumn('{{%all_map}}', 'created_at');
        $this->dropCommentFromColumn('{{%all_map}}', 'linked_at');
        $this->dropCommentFromColumn('{{%all_map}}', 'updated_at');
    }
}
