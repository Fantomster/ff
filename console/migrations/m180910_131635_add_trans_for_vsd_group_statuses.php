<?php

use yii\db\Migration;

/**
 * Class m180910_131635_add_trans_for_vsd_group_statuses
 */
class m180910_131635_add_trans_for_vsd_group_statuses extends Migration
{
    public $translations_ru = [
        'vsd_status_withdrawn' => 'Сертификаты аннулированы',
        'vsd_status_confirmed' => 'Сертификаты ожидают погашения',
        'vsd_status_utilized' => 'Сертификаты погашены',
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
