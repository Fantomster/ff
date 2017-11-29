<?php

use yii\db\Migration;

/**
 * Class m171128_123753_sms_text
 */
class m171128_123753_sms_text extends Migration
{
    private $file = '/files/sms_text.csv';
    private $category = 'sms_message';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $file = $this->getFile();
        foreach ($file as $source_message => $messages) {
            $this->insert('source_message', ['category' => $this->category, 'message' => $source_message]);
            $id = $this->getIdSourceMessage($source_message);
            if (is_numeric($id) && $id > 0) {
                foreach ($messages as $lang => $message) {
                    $params = ['id' => $id, 'language' => $lang];
                    if (!empty($message)) {
                        $params['translation'] = $message;
                    }
                    $this->insert('message', $params);
                }
            } else {
                echo 'error id ' . $id;
                return false;
            }
            echo 'add ' . $source_message . ' success' . PHP_EOL;
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $file = $this->getFile();
        foreach ($file as $source_message => $messages) {
            $id = $this->getIdSourceMessage($source_message);
            $this->delete('message', ['id' => $id]);
            $this->delete('source_message', ['id' => $id]);
        }
        return true;
    }

    /**
     * Данные из CSV
     * @return array
     */
    private function getFile()
    {
        $file = __DIR__ . $this->file;
        $handle = fopen($file, "r");
        $array_line_full = [];
        while (($line = fgetcsv($handle, 0, ";")) !== FALSE) {
            if (isset($line) && is_array($line)) {
                if (isset($line[0]) && isset($line[1]) && isset($line[2])) {
                    if ($line[2] == '' or empty(trim($line[2]))) {
                        $line[2] = null;
                    }
                    $array_line_full[trim($line[0])][trim($line[1])] = trim($line[2]);
                }
            }
        }
        fclose($handle); //Закрываем файл
        return $array_line_full;
    }

    /**
     * ID записи из source_message
     * @param $source_message
     * @return integer
     */
    private function getIdSourceMessage($source_message)
    {
        $sql = "SELECT id FROM source_message WHERE category=:c AND message=:m";
        $id = Yii::$app->db->createCommand($sql)
            ->bindValue(':c', $this->category)
            ->bindValue(':m', $source_message)
            ->queryScalar();
        return $id;
    }
}
