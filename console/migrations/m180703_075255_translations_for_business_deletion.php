<?php

use yii\db\Migration;

/**
 * Class m180703_075255_translations_for_business_deletion
 */
class m180703_075255_translations_for_business_deletion extends Migration
{
    public $translations = [
        'frontend.controllers.user.business_deleted' => 'Бизнес успешно удален!',
        'frontend.controllers.user.business_delete_question' => 'Действительно удалить бизнес?',
        'frontend.controllers.user.business_cancel_btn' => 'Отмена',
        'frontend.controllers.user.business_confirm_btn' => 'Удалить',
        'frontend.controllers.user.business_error_title' => 'Ошибка!',
        'frontend.controllers.user.business_error_text' => 'Произошла неизвестная ошибка',
    ];
    
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        \console\helpers\BatchTranslations::insertCategory('ru', 'app', $this->translations);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'app', $this->translations);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180703_075255_translations_for_business_deletion cannot be reverted.\n";

        return false;
    }
    */
}
