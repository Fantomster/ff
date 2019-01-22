\<?php

use api_web\exceptions\ValidationException;
use common\models\Message;
use common\models\SourceMessage;
use yii\db\Migration;

/**
 * Class m190121_133330_change_translation
 */
class m190121_133330_change_translation extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sMessage = SourceMessage::findOne(['message' => 'frontend.controllers.order.mea']);
        $message = Message::findOne(['id' => $sMessage->id, 'language' => 'ru']);
        $message->translation = 'Ед изм';
        if (!$message->save()) {
            throw new ValidationException($message->getFirstErrors());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190121_133330_change_translation cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190121_133330_change_translation cannot be reverted.\n";

        return false;
    }
    */
}
