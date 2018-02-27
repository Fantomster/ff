<?php

use yii\db\Migration;

/**
 * Class m180219_132841_update_translate_data_six
 */
class m180219_132841_update_translate_data_six extends Migration
{
    private $file = '/files/countries_spanish_translation.csv';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $data = $this->getData();
        foreach ($data as $ru_country => $messages) {
            foreach ($messages as $category => $value) {
                $sourceMessage = \common\models\SourceMessage::findOne(['message'=>$ru_country, 'category'=>$category]);
                $id = $sourceMessage->id;
                if (is_numeric($id) && $id > 0) {
                    $this->update('message', ['translation'=>$value], ['language'=>'es', 'id'=>$id]);
                } else {
                    echo 'error id ' . $id;
                    return false;
                }
                echo 'add ' . $value . ' success' . PHP_EOL;
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
                if (isset($line[0]) && isset($line[1]) && isset($line[2])) {
                        $array_line_full[$line[1]][$line[0]] = $line[2];
                }
            }
        }
        fclose($handle); //Закрываем файл
        return $array_line_full;
    }
}
