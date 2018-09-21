<?php

use yii\db\Migration;

/**
 * Class m180918_121544_init_vetis_packing_type
 */
class m180918_121544_init_vetis_packing_type extends Migration
{
    private $file = '/files/packing_type.csv';

    public function init()
    {
        $this->db = "db_api";
        parent::init();
    }
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $file = __DIR__ . $this->file;
        $r = 0;
        $handle = fopen($file, "r");
        // $handle - путь к файлу и его имя, 1000 - длина, ; - разделитель
        echo "<pre>";
        while (($row = fgetcsv($handle, 1000, "|")) != FALSE){
            $r++;
            if($r == 1) {continue;} // Не импортируем первую строку (например, если там заголовки)
            //записываем данные в БД
            $params = ['uuid' => $row[0], 'guid' => $row[1], 'name' => $row[2], 'globalID' => $row[3]];
            $this->insert('{{%vetis_packing_type}}', $params);
        }
        fclose($handle);
        //throw ne
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180918_121544_init_vetis_packing_type cannot be reverted.\n";

        return false;
    }

    private function ucfirst_utf8($str)
    {
        return mb_substr(mb_strtoupper($str, 'utf-8'), 0, 1, 'utf-8') . mb_substr($str, 1, mb_strlen($str)-1, 'utf-8');
    }
}
