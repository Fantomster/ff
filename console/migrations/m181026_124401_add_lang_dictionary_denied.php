<?php

use yii\db\Migration;

/**
 * Class m181026_124401_add_lang_dictionary_denied
 */
class m181026_124401_add_lang_dictionary_denied extends Migration
{
    public $translations_ru = [
        'dictionary.access_denied' => 'Доступ к справочнику организации запрещен'
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        \console\helpers\BatchTranslations::insertCategory('ru', 'api_web', $this->translations_ru);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'api_web', $this->translations_ru);
    }
}
