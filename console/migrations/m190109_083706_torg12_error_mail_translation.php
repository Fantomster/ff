<?php

use yii\db\Migration;

/**
 * Class m190109_083706_torg12_error_mail_translation
 */
class m190109_083706_torg12_error_mail_translation extends Migration
{
    public $translations = [
        'common.mail.error.errorsum1' => 'Возможно, в Вашей накладной ошибка. Просим проверить.',
        'common.mail.error.errorsum2' => 'В вашем письме, отправленном',
        'common.mail.error.errorsum3' => 'во вложенном файле накладной',
        'common.mail.error.errorsum4' => 'есть ошибки. Суммы, указанные в итоге накладной, не совпадают с подсчитанной суммой всех строк накладной.',
        'common.mail.error.errorsum5' => 'Сумма накладной без НДС -',
        'common.mail.error.errorsum6' => ', а сумма без НДС всех строк накладной равна',
        'common.mail.error.errorsum7' => 'Сумма накладной c НДС -',
        'common.mail.error.errorsum8' => ', а сумма c НДС всех строк накладной равна',
        'common.mail.error.errorsum9' => 'Просим обратить внимание на ошибку и подтвердить достоверность передаваемых данных.',
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        \console\helpers\BatchTranslations::insertCategory('ru', 'message', $this->translations);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'message', $this->translations);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190109_083706_torg12_error_mail_translation cannot be reverted.\n";

        return false;
    }
    */
}
