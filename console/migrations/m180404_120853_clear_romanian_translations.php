<?php

use yii\db\Migration;

/**
 * Class m180404_120853_clear_romanian_translations
 */
class m180404_120853_clear_romanian_translations extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('source_message', 'message', 'varchar(255)');
        $sourceMessages = \common\models\SourceMessage::find()->orderBy(['id'=>'desc'])->all();
        foreach ($sourceMessages as $sourceMessage){
            $count = \common\models\SourceMessage::find()->where(['message'=>$sourceMessage->message, 'category'=>$sourceMessage->category])->count();
            if($count>1 && (\common\models\Message::find()->where(['id'=>$sourceMessage->id])->count() < 4)){
                $this->delete('source_message', ['id'=>$sourceMessage->id]);
                $this->delete('message', ['id'=>$sourceMessage->id]);
            }
        }

        foreach ($sourceMessages as $sourceMessage){
            $count = \common\models\SourceMessage::find()->where(['message'=>$sourceMessage->message, 'category'=>$sourceMessage->category])->count();
            if($count>1){
                $this->delete('source_message', ['id'=>$sourceMessage->id]);
                $this->delete('message', ['id'=>$sourceMessage->id]);
            }
        }

        $this->createIndex('unique_message_category', 'source_message', ['message', 'category'], true);
        $messages = \common\models\Message::findAll(['language'=>'md']);
        foreach ($messages as $message){
            $id = $message->id;
            if (is_numeric($id) && $id > 0) {
                $mess = \common\models\Message::findOne(['language'=>'ru', 'id'=>$id]);
                if(!$mess){
                    $this->delete('message', ['id'=>$id, 'language'=>'md']);
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
        $this->dropIndex('unique_message_category', 'source_message');
        $this->alterColumn('source_message', 'message', 'text');
        return true;
    }
}
