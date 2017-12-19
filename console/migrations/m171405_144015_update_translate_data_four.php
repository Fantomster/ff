<?php

use yii\db\Migration;

/**
 * Class m171129_144015_fill_translate_data
 */
class m171405_144015_update_translate_data_four extends Migration
{
    private $file = '/files/translate_text_four.csv';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();
        $ids = (new \yii\db\Query())->select(['id'])->from('source_message')->where(['category'=>['app', 'message', 'error']])->all();
        foreach ($ids as $one){
            foreach ($one as $id){
                Yii::$app->db->createCommand('DELETE FROM `source_message` WHERE `id`='.$id)->execute();
                Yii::$app->db->createCommand('DELETE FROM `message` WHERE `id`='.$id)->execute();
            }
        }
        Yii::$app->db->createCommand()->checkIntegrity(true)->execute();

        $data = $this->getData();
        foreach ($data as $message => $messages) {
            foreach ($messages as $category => $value) {
                $this->insert('source_message', ['category' => $category, 'message' => $message]);
                $id = $this->getIdSourceMessage($category, $message);
                if (is_numeric($id) && $id > 0) {
                    foreach ($value as  $lang => $translation) {
                        $params = ['id' => $id, 'language' => $lang];
                        if (!empty($translation)) {
                            $params['translation'] = $translation;
                        }
                        $count = (new \yii\db\Query())->select(['id'])->from('message')->where(['id'=>$id, 'language'=>$lang])->all();
                        if(count($count)<1){
                            $this->insert('message', $params);
                        }
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
        $file = $this->getData();
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
    private function getData()
    {
        $file = __DIR__ . $this->file;
        $handle = fopen($file, "r");
        $array_line_full = [];
        while (($line = fgetcsv($handle, 0, "|")) !== FALSE) {
            if (isset($line) && is_array($line)) {
                if (isset($line[0]) && isset($line[1]) && isset($line[2]) && isset($line[3])) {
                    if ($line[3] == '' or empty($line[3]) or $line[3]=='NULL' or $line[3]=='null') {
                        $array_line_full[$line[1]][$line[0]][$line[2]] = NULL;
                    }else{
                        $array_line_full[$line[1]][$line[0]][$line[2]] = $line[3];
                    }
                }
            }
        }
        fclose($handle); //Закрываем файл
//        $array = (new \yii\db\Query())->select(['name'])->from('mp_ed')->where(['!=', 'name', ''])->all();
//        $array = array_merge($array, (new \yii\db\Query())->select(['name'])->from('category')->where(['!=', 'name', ''])->all());
//        $array = array_merge($array, (new \yii\db\Query())->select(['text'])->from('currency')->where(['!=', 'text', ''])->all());
//        $array = array_merge($array, (new \yii\db\Query())->select(['name'])->from('franchise_type')->where(['!=', 'name', ''])->all());
//        $array = array_merge($array, (new \yii\db\Query())->select(['name'])->from('mp_category')->where(['!=', 'name', ''])->all());
//        $array = array_merge($array, (new \yii\db\Query())->select(['name'])->from('mp_country')->where(['!=', 'name', ''])->all());
//        $array = array_merge($array, (new \yii\db\Query())->select(['full_name'])->from('mp_country')->where(['!=', 'full_name', ''])->all());
//        $array = array_merge($array, (new \yii\db\Query())->select(['location'])->from('mp_country')->where(['!=', 'location', ''])->all());
//        $array = array_merge($array, (new \yii\db\Query())->select(['name'])->from('organization_type')->where(['!=', 'name', ''])->all());
//        $array = array_merge($array, (new \yii\db\Query())->select(['name'])->from('role')->where(['!=', 'name', ''])->all());
//        $array = array_merge($array, (new \yii\db\Query())->select(['text'])->from('sms_status')->where(['!=', 'text', ''])->all());
//        foreach ($array as $one){
//            foreach ($one as $name){
//                $array_line_full[$name]['app']['ru'] = $name;
//                $array_line_full[$name]['app']['en'] = null;
//                $array_line_full[$name]['app']['es'] = null;
//            }
//        }
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
