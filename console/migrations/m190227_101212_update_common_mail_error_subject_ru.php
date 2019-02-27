<?php

use yii\db\Migration;
use common\models\SourceMessage;

class m190227_101212_update_common_mail_error_subject_ru extends Migration
{
    public function safeUp()
    {
        $sm = SourceMessage::find()->where(['category' => 'app', 'message' => 'common.mail.error.subject'])->one();
        $this->update('{{%message}}', [
            'translation' => 'В вашей накладной ошибка!'],
        ['language' => 'ru', 'id' => $sm->id]
        );
    }

    public function safeDown()
    {
        echo "m190227_101212_update_common_mail_error_subject_ru cannot be reverted.\n";
        return false;
    }
}
