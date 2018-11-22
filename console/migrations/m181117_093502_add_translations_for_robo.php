<?php

use yii\db\Migration;

/**
 * Class m181117_093502_add_translations_for_robo
 */
class m181117_093502_add_translations_for_robo extends Migration
{
    public $translations = [
        'integration.email.bad_organization_id' => 'Вам не хватает прав для редактирования настроек данного бизнеса',
        'vendor.is_work'                        => 'Данный поставщик уже работает в нашей системе, свяжитесь с ним для назначения персонального каталога',
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        \console\helpers\BatchTranslations::insertCategory('ru', 'api_web', $this->translations);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'api_web', $this->translations);
    }
}
