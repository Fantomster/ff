<?php

use yii\db\Migration;

/**
 * Class m181025_123638_translations_for_waybill_delete
 */
class m181025_123638_translations_for_waybill_delete extends Migration
{
    public $translations = [
        'waybill.waibill_not_found' => 'Накладная не найдена',
        'waybill.waibill_not_relation_this_service' => 'Накладная не связана с заданным сервисом',
        'waybill.waibill_is_unloading' => 'Накладная в статусе выгружена',
        'waybill.waibill_is_relation_order' => 'Накладная связана с заказом',
        'waybill.waibill_not_releated_current_user' => 'Накладная не пренадлежит организациям текущего пользователя',
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
