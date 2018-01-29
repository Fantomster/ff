<?php

use yii\db\Migration;

/**
 * Class m180112_073009_update_currency_table
 */
class m180112_073009_update_currency_table extends Migration
{

    private $file = '/files/currency.csv';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%currency}}', 'is_active', $this->boolean()->notNull()->defaultValue(true));
        $this->addColumn('{{%currency}}', 'old_symbol', $this->string(255));
        $this->alterColumn('{{%currency}}', 'num_code', 'varchar(255) COLLATE utf8_unicode_ci NOT NULL');
        $arr = [];
        foreach((new \yii\db\Query())->from('currency')->each() as $currency) {
            $arr[] = $currency['iso_code'];
            $this->update('{{%currency}}', ['old_symbol' => $currency['symbol'], 'symbol' => $currency['iso_code']], ['id' => $currency['id']]);
        }

        $array = $this->getData();
        foreach ($array as $num_code=>$currency){
            foreach ($currency as $iso_code=>$name){
                if(!in_array($iso_code, $arr)){
                    $params = ['text' => $name, 'symbol' => $iso_code, 'num_code' => $num_code, 'iso_code' => $iso_code, 'signs' => 2, 'is_active' => 0];
                    $this->insert('{{%currency}}', $params);
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        foreach((new \yii\db\Query())->from('currency')->each() as $currency) {
            $this->update('{{%currency}}', ['symbol' => $currency['old_symbol']], ['id' => $currency['id']]);
        }
        $this->dropColumn('{{%currency}}', 'is_active');
        $this->dropColumn('{{%currency}}', 'old_symbol');
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
        $i=0;
        while (($line = fgetcsv($handle, 0, "|")) !== FALSE) {
            $i++;
            if($i==1 || $i==2)continue;
            if (isset($line) && is_array($line)) {
                if (isset($line[0]) && isset($line[1]) && isset($line[2])) {
                        $array_line_full[$line[0]][$line[1]] = $this->ucfirst_utf8(mb_strtolower($line[2]));
                }
            }
        }
        fclose($handle); //Закрываем файл
        return $array_line_full;
    }

    private function ucfirst_utf8($str)
    {
        return mb_substr(mb_strtoupper($str, 'utf-8'), 0, 1, 'utf-8') . mb_substr($str, 1, mb_strlen($str)-1, 'utf-8');
    }
}
