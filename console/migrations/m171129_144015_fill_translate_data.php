<?php

use yii\db\Migration;

/**
 * Class m171129_144015_fill_translate_data
 */
class m171129_144015_fill_translate_data extends Migration
{
    private $file = '/files/translate_text.csv';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $file = $this->getFile();
        foreach ($file as $message => $messages) {
            foreach ($messages as $category => $value) {
                $this->insert('source_message', ['category' => $category, 'message' => $message]);
                $id = $this->getIdSourceMessage($category, $message);
                if (is_numeric($id) && $id > 0) {
                    $count = 0;
                    foreach ($value as  $translation => $lang) {
                        $params = ['id' => $id, 'language' => $lang];
                        if (!empty($translation)) {
                            $params['translation'] = $translation;
                        }
                        $count = (new \yii\db\Query())->from('message')->where(['id'=>$id, 'language'=>$lang])->count();
                        if($count)continue;
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
                    if ($line[3] == '' or empty(trim($line[3]))) {
                        $line[3] = null;
                    }
                    if(strpos($line[1], '@@'))continue;
                    $array_line_full[$line[1]][$line[0]][$line[3]] = $line[2];
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
