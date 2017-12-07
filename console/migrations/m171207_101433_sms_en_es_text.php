<?php

use yii\db\Migration;

/**
 * Class m171207_101433_sms_en_es_text
 */
class m171207_101433_sms_en_es_text extends Migration
{
    private $file = '/files/sms_en_es_text.csv';
    //Категория сообщений
    private $category = 'sms_message';
    //Сообщения для удаления
    private $delete_messages = [];

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        //Удаляем лишние сообщения
        if (isset($this->delete_messages) && !empty($this->delete_messages)) {
            foreach ($this->delete_messages as $row) {
                $id = $this->getIdSourceMessage($row);
                if (isset($id)) {
                    if($this->existsMessage($id)) {
                        $this->delete('message', ['id' => $id]);
                    }
                    $this->delete('source_message', ['id' => $id]);
                    echo 'DELETE :' . $row . ' success' . PHP_EOL;
                }
            }
        }
        //Обновляем сообщения из CSV
        $file = $this->getFile();
        foreach ($file as $source_message => $messages) {
            $id = $this->getIdSourceMessage($source_message);

            if(!$id) {
                $this->insert('source_message', ['category' => $this->category, 'message' => $source_message]);
                $id = $this->getIdSourceMessage($source_message);
            }

            if (is_numeric($id) && $id > 0) {
                foreach ($messages as $lang => $message) {
                    $params = ['id' => $id, 'language' => $lang];
                    if($this->existsMessage($id, $lang)){
                        $this->update('message', ['translation' => $message], $params);
                        echo 'UPDATE MESSAGE:' . $source_message . ' - ' . $lang . ' success' . PHP_EOL;
                    } else {
                        $params['translation'] = $message;
                        $this->insert('message', $params);
                        echo 'INSERT MESSAGE:' . $source_message . ' - ' . $lang . ' success' . PHP_EOL;
                    }
                }
            } else {
                echo 'ERROR update source_message ' . $source_message;
                return false;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171204_131125_sms_text_edit cannot be reverted.\n";
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

    /**
     * @param $id
     * @param $lang
     * @return bool
     */
    private function existsMessage($id, $lang = 'ru')
    {
        $sql = "SELECT id FROM message WHERE id = :id AND language = :l";
        return (bool) Yii::$app->db->createCommand($sql)
            ->bindValue(':id', $id)
            ->bindValue(':l', $lang)
            ->queryScalar();
    }
}
