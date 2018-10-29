<?php

use yii\db\Migration;

/**
 * Class m181023_150159_translations_for_organization_model
 */
class m181023_150159_translations_for_organization_model extends Migration
{
    public $translations = [
        'common.models.organization_name_error' => 'Пожалуйста, напишите название вашей организации',
        'common.models.organization_name_error2' => 'Пожалуйста, напишите название организации',
        'common.models.organization_type_id_error' => 'Укажите, Вы покупаете или продаете?',
        'common.models.organization_address_error' => 'Установите точку на карте, путем ввода адреса в поисковую строку.',
        'common.models.organization_inn_error' => 'Поле должно состоять из 10 или 12 цифр',
        'common.models.organization_kpp_error' => 'Поле должно состоять из 9 цифр',
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp() {
        \console\helpers\BatchTranslations::insertCategory('ru', 'app', $this->translations);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown() {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'app', $this->translations);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181023_150159_translations_for_organization_model cannot be reverted.\n";

        return false;
    }
    */
}
