<?php

use yii\db\Migration;

/**
 * Class m180703_150451_translations_for_additional_email
 */
class m180703_150451_translations_for_additional_email extends Migration {

    public $translations = [
        'frontend.views.settings.email_not_confirmed' => 'Почта не подтверждена',
        'frontend.views.settings.new_email_confirm' => 'На новый email выслано письмо для подтверждения',
        'common.models.additional_mail_subject' => 'Дополнительная почта для MixCart',
        'frontend.views.user.default.incorrect_url' => 'Некорректная ссылка',
        'frontend.views.user.default.additional_email_confirmed' => 'Дополнительная почта подтверждена!',
        'common.mail.confirm_additional_email.full_text' => 'Чтобы использовать дополнительный адрес электронной почты, его необходимо подтвердить. Для этого пройдите по ссылке:',
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
      echo "m180703_150451_translations_for_additional_email cannot be reverted.\n";

      return false;
      }
     */
}
