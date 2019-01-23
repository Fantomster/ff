<?php

use yii\db\Migration;

class m190110_111513_add_comments_table_egais_product_on_balance extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `egais_product_on_balance` comment "Таблица сведений об отдельных товарах, поставленных на баланс в системе ЕГАИС";');
        $this->addCommentOnColumn('{{%egais_product_on_balance}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%egais_product_on_balance}}', 'org_id', 'Идентификатор организации');
        $this->addCommentOnColumn('{{%egais_product_on_balance}}', 'quantity', 'Количество товара');
        $this->addCommentOnColumn('{{%egais_product_on_balance}}', 'inform_a_reg_id', 'Идентификатор товара в базе ЕГАИС по форме 1');
        $this->addCommentOnColumn('{{%egais_product_on_balance}}', 'inform_b_reg_id', 'Идентификатор товара в базе ЕГАИС по форме 2');
        $this->addCommentOnColumn('{{%egais_product_on_balance}}', 'full_name', 'Полное наименование товара');
        $this->addCommentOnColumn('{{%egais_product_on_balance}}', 'alc_code', 'Алкокод товара');
        $this->addCommentOnColumn('{{%egais_product_on_balance}}', 'capacity', 'Объём товара');
        $this->addCommentOnColumn('{{%egais_product_on_balance}}', 'alc_volume', 'Содержание спирта в товаре в процентах');
        $this->addCommentOnColumn('{{%egais_product_on_balance}}', 'product_v_code', 'Идентификатор типа акцизной марки');
        $this->addCommentOnColumn('{{%egais_product_on_balance}}', 'producer_client_reg_id', 'Идентификатор производителя товара');
        $this->addCommentOnColumn('{{%egais_product_on_balance}}', 'producer_inn', 'ИНН проивзодителя товара');
        $this->addCommentOnColumn('{{%egais_product_on_balance}}', 'producer_kpp', 'КПП производителя товара');
        $this->addCommentOnColumn('{{%egais_product_on_balance}}', 'producer_full_name', 'Полное наименование производителя товара');
        $this->addCommentOnColumn('{{%egais_product_on_balance}}', 'producer_short_name', 'Краткое наименование производителя товара');
        $this->addCommentOnColumn('{{%egais_product_on_balance}}', 'address_country', 'Идентификатор государства производителя товара');
        $this->addCommentOnColumn('{{%egais_product_on_balance}}', 'address_region_code', 'Идентификатор региона производителя товара');
        $this->addCommentOnColumn('{{%egais_product_on_balance}}', 'address_description', 'Адрес производителя товара');
    }

    public function safeDown()
    {
        $this->execute('alter table `egais_product_on_balance` comment "";');
        $this->dropCommentFromColumn('{{%egais_product_on_balance}}', 'id');
        $this->dropCommentFromColumn('{{%egais_product_on_balance}}', 'org_id');
        $this->dropCommentFromColumn('{{%egais_product_on_balance}}', 'quantity');
        $this->dropCommentFromColumn('{{%egais_product_on_balance}}', 'inform_a_reg_id');
        $this->dropCommentFromColumn('{{%egais_product_on_balance}}', 'inform_b_reg_id');
        $this->dropCommentFromColumn('{{%egais_product_on_balance}}', 'full_name');
        $this->dropCommentFromColumn('{{%egais_product_on_balance}}', 'alc_code');
        $this->dropCommentFromColumn('{{%egais_product_on_balance}}', 'capacity');
        $this->dropCommentFromColumn('{{%egais_product_on_balance}}', 'alc_volume');
        $this->dropCommentFromColumn('{{%egais_product_on_balance}}', 'product_v_code');
        $this->dropCommentFromColumn('{{%egais_product_on_balance}}', 'producer_client_reg_id');
        $this->dropCommentFromColumn('{{%egais_product_on_balance}}', 'producer_inn');
        $this->dropCommentFromColumn('{{%egais_product_on_balance}}', 'producer_kpp');
        $this->dropCommentFromColumn('{{%egais_product_on_balance}}', 'producer_full_name');
        $this->dropCommentFromColumn('{{%egais_product_on_balance}}', 'producer_short_name');
        $this->dropCommentFromColumn('{{%egais_product_on_balance}}', 'address_country');
        $this->dropCommentFromColumn('{{%egais_product_on_balance}}', 'address_region_code');
        $this->dropCommentFromColumn('{{%egais_product_on_balance}}', 'address_description');
    }
}
