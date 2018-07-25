<?php

use yii\db\Migration;

/**
 * Class m180723_155619_add_translations_for_main_catalog
 */
class m180723_155619_add_translations_for_main_catalog extends Migration
{
    public $translations = [
        'frontend.views.vendor.delete_all' => 'Удалить все',
        'frontend.views.vendor.catalog_deleted' => 'Каталог удален!',
        'frontend.views.vendor.index_changed' => 'Индекс изменен!',
        'frontend.views.vendor.catalog_not_empty' => 'Каталог не пустой',
        'frontend.views.vendor.catalog_deletion_failed' => 'Не удалось удалить каталог',
        'frontend.views.vendor.btn_delete_restore' => 'Удалить/восстановить каталог',
        'frontend.views.vendor.btn_delete' => 'Удалить каталог',
        'frontend.views.vendor.btn_restore' => 'Восстановить последнюю сохраненную копию каталога',
        'frontend.views.vendor.restore_catalog' => 'Восстановить каталог',
        'frontend.views.vendor.catalog_restoration_failed' => 'Не удалось восстановить каталог',
        'frontend.views.vendor.catalog_restored' => 'Каталог восстановлен!',
        'frontend.views.vendor.change_index' => 'Изменить индекс:',
        'frontend.views.vendor.index_reject' => 'Этот индекс уже используется',
        'message', 'frontend.views.vendor.ssid' => 'SSID',
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp() {
        \console\helpers\BatchTranslations::insertCategory('ru', 'message', $this->translations);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown() {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'message', $this->translations);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180723_155619_add_translations_for_main_catalog cannot be reverted.\n";

        return false;
    }
    */
}
