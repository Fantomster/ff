<?php

use yii\db\Migration;

/**
 * Class m180524_071249_create_ukraian_translations
 */
class m180524_071249_create_ukraian_translations extends Migration
{
    private $file = '/files/file_for_ukranian_translation.csv';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $data = $this->getData();
        foreach ($data as $ru_message => $md_message) {
            $messages = \common\models\Message::findAll(['translation'=>$ru_message]);
            foreach ($messages as $message){
                $id = $message->id;
                if (is_numeric($id) && $id > 0) {
                    $mess = \common\models\Message::findOne(['language'=>'ua', 'id'=>$id]);
                    if(!$mess){
                        $this->insert('message', ['translation'=>$md_message, 'language'=>'ua', 'id'=>$id]);
                    }
                } else {
                    echo 'error id ' . $id;
                    return false;
                }
            }
        }
        $sourceMessages = \common\models\SourceMessage::find()->all();
        foreach ($sourceMessages as $sourceMessage){
            $mess = \common\models\Message::findOne(['language'=>'ua', 'id'=>$sourceMessage->id]);
            if(!$mess){
                $this->insert('message', ['translation'=>'', 'language'=>'ua', 'id'=>$sourceMessage->id]);
            }
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        return true;
    }

    /**
     * Данные из CSV
     * @return array
     */
    private function getData()
    {
        $file = __DIR__ . $this->file;
        $handle = fopen($file, "r");
        $array_line_full = [];
        while (($line = fgetcsv($handle, 0, "|")) !== FALSE) {
            if (isset($line) && is_array($line)) {
                if (isset($line[2]) && isset($line[3])) {
                    $array_line_full[$line[2]] = $line[3];
                }
            }
        }
        fclose($handle); //Закрываем файл
        return $array_line_full;
    }
}
