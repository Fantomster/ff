<?php

use yii\db\Migration;

/**
 * Class m171129_144015_fill_translate_data
 */
class m171130_144015_update_translate_data extends Migration
{
    private $file = '/files/translate_text_two.csv';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();
        Yii::$app->db->createCommand()->truncateTable('message', false)->execute();
        Yii::$app->db->createCommand()->truncateTable('source_message')->execute();
        Yii::$app->db->createCommand()->checkIntegrity(true)->execute();
        $file = $this->getFile();
        foreach ($file as $message => $messages) {
            foreach ($messages as $category => $value) {
                $this->insert('source_message', ['category' => $category, 'message' => $message]);
                $id = $this->getIdSourceMessage($category, $message);
                if (is_numeric($id) && $id > 0) {
                    foreach ($value as  $lang => $translation) {
                        $params = ['id' => $id, 'language' => $lang];
                        if (!empty($translation)) {
                            $params['translation'] = $translation;
                        }
                        $this->insert('message', $params);
                    }
                } else {
                    echo 'error id ' . $id;
                    return false;
                }
                echo 'add ' . $message . ' success' . PHP_EOL;
            }
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $file = $this->getFile();
        foreach ($file as $category => $messages) {
            foreach ($messages as $message=>$value) {
                $id = $this->getIdSourceMessage($category, $message);
                $this->delete('message', ['id' => $id]);
                $this->delete('source_message', ['id' => $id]);
            }
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
        while (($line = fgetcsv($handle, 0, "|")) !== FALSE) {
            if (isset($line) && is_array($line)) {
                if (isset($line[0]) && isset($line[1]) && isset($line[2]) && isset($line[3])) {
                    if ($line[3] == '' or empty($line[3])) {
                        $line[3] = null;
                    }
                    $array_line_full[$line[1]][$line[0]][$line[2]] = $line[3];
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
    private function getIdSourceMessage($category, $message)
    {
        $sql = "SELECT id FROM source_message WHERE category=:c AND message=:m";
        $id = Yii::$app->db->createCommand($sql)
            ->bindValue(':c', $category)
            ->bindValue(':m', $message)
            ->queryScalar();
        return $id;
    }
}
