<?php

use yii\db\Migration;

/**
 * Class m180905_111739_add_service_operations
 */
class m180905_111739_add_service_operations extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->batchInsert('all_service_operation', ['service_id', 'code', 'denom', 'comment'], [
            ['4', '14', 'MercBusinessEntityList', 'Обновление списка хозяйствующих субъектов'],
            ['4', '15', 'MercCountryList', 'Обновление списка всех стран'],
            ['4', '16', 'MercForeignEnterpriseList', 'Обновление списка иностранных предприятий'],
            ['4', '17', 'MercProductItemList', 'Обновление справочника наименований продукции для предприятия-производителя'],
            ['4', '18', 'MercProductList', 'Обновление справочника продукции'],
            ['4', '19', 'MercRussianEnterpriseList', 'Обновление списка Российских предприятий'],
            ['4', '20', 'MercSubProductList', 'Обновление справочника видов продукции'],
            ['4', '21', 'MercPurposeList', 'Обновление списка целей'],
            ['4', '22', 'MercUnitList', 'Обновление списка единиц измерения'],
            ['4', '23', 'MercVSDList', 'Обновление списка ВСД'],
            ['4', '24', 'MercStockEntryList', 'обновление записей складского журнала'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180905_111739_add_service_operations cannot be reverted.\n";

        return false;
    }

}
