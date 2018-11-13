<?php

use common\models\SourceMessage;
use yii\db\Migration;

/**
 * Class m181020_140421_rename_category_for_translate_waybill
 */
class m181020_140421_rename_category_for_translate_waybill extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $messages = SourceMessage::findAll(['category' => 'web_api']);
        foreach ($messages as $message) {
            $message->category = 'api_web';
            $message->save();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181020_140421_rename_category_for_translate_waybill cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181020_140421_rename_category_for_translate_waybill cannot be reverted.\n";

        return false;
    }
    */
}
