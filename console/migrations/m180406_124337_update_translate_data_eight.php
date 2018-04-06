<?php

use yii\db\Migration;

/**
 * Class m180406_124337_update_translate_data_eight
 */
class m180406_124337_update_translate_data_eight extends Migration
{
    private $file = '/files/file_for_moldovian_translations_two.csv';

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
                    $this->update('message', ['translation' => $md_message], ['id' => $id, 'language' => 'md']);
                }
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
                if (isset($line[1]) && isset($line[2])) {
                    $array_line_full[$line[1]] = $line[2];
                }
            }
        }
        fclose($handle); //Закрываем файл
        return $array_line_full;
    }
}
