<?php

use yii\db\Migration;

/**
 * Class m181030_115820_translation_for_documents
 */
class m181030_115820_translation_for_documents extends Migration
{
    public $translations = [
        'doc_group.forming' => 'Ожидает формирования',
        'doc_group.sending' => 'Ожидает выгрузки',
        'doc_group.sent' => 'Выгружен',
        'doc_order.doc_number'  => 'Номеру документа А-Я',
        'doc_order.-doc_number' => 'Номеру документа Я-А',
        'doc_order.doc_date'    => 'Дате документа по возрастанию',
        'doc_order.-doc_date'   => 'Дате документа по убванию',
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp() {
        \console\helpers\BatchTranslations::insertCategory('ru', 'api_web', $this->translations);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown() {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'api_web', $this->translations);
    }
}
