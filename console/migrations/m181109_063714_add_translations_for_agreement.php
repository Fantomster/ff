<?php

use yii\db\Migration;

/**
 * Class m181109_063714_add_translations_for_agreement
 */
class m181109_063714_add_translations_for_agreement extends Migration
{
    public $translations = [
        'user.cannot_disable_accepted_agreement' => 'Невозможно отменить принятое соглашение',
        'common.wrong_agreement_name'   => 'Неверно задан параметр, см. описание.',
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
        \console\helpers\BatchTranslations::deleteCategory('ru', 'api)_web', $this->translations);
    }
}
