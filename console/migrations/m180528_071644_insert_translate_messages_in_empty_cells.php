<?php

use yii\db\Migration;

/**
 * Class m180528_071644_insert_translate_messages_in_empty_cells
 */
class m180528_071644_insert_translate_messages_in_empty_cells extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $languages = ['en', 'es', 'md', 'ua'];
        $messages = \common\models\Message::findAll(['language'=>'ru']);
        foreach ($messages as $message){
            $id = $message->id;
            if (is_numeric($id) && $id > 0) {
                foreach ($languages as $language){
                    $mess = \common\models\Message::findOne(['language'=>$language, 'id'=>$id]);
                    if(!$mess){
                        $this->insert('message', ['translation'=>'', 'language'=>$language, 'id'=>$id]);
                    }
                }
            } else {
                echo 'error id ' . $id;
                return false;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180528_071644_insert_translate_messages_in_empty_cells cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180528_071644_insert_translate_messages_in_empty_cells cannot be reverted.\n";

        return false;
    }
    */
}
