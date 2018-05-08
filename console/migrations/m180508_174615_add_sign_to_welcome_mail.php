<?php

use yii\db\Migration;

/**
 * Class m180508_174615_add_sign_to_welcome_mail
 */
class m180508_174615_add_sign_to_welcome_mail extends Migration
{
    /**
     * {@inheritdoc}
     */
    public $ru = [
        ['common.mail.welcome.sign_1', 'Ильдар Хасанов'],
        ['common.mail.welcome.sign_2', 'Сооснователь MixCart'],
    ];
    
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        foreach ($this->ru as $row) {
            $key = trim($row[0]);
            $value = trim($row[1]);

            $source_message = \common\models\SourceMessage::findOne(['message' => $key, 'category' => 'app']);

            if (empty($source_message)) {
                $source_message = new \common\models\SourceMessage();
                $source_message->message = $key;
                $source_message->category = 'app';
                $source_message->save();
            }

            $message = \common\models\Message::findOne(['id' => $source_message->id, 'language' => 'ru']);
            if (empty($message)) {
                $message = new \common\models\Message();
            }

            $message->id = $source_message->id;
            $message->language = 'ru';
            $message->translation = $value;
            $message->save();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        foreach ($this->ru as $row) {
            $key = trim($row[0]);
            $source_message = \common\models\SourceMessage::findOne(['message' => $key, 'category' => 'app']);
            if (!empty($source_message)) {
                $message = \common\models\Message::findOne(['id' => $source_message->id, 'language' => 'ru']);
                if (!empty($message)) {
                    $message->delete();
                }
                $source_message->delete();
            }
        }
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180508_174615_add_sign_to_welcome_mail cannot be reverted.\n";

        return false;
    }
    */
}
