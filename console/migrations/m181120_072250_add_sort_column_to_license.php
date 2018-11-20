<?php

use yii\db\Migration;

/**
 * Class m181120_072250_add_sort_column_to_license
 */
class m181120_072250_add_sort_column_to_license extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('license', 'sort_index', $this->integer()->null());
        $this->addCommentOnColumn('license', 'sort_index', 'Индекс сортировки');
        $array = [
            '10' => 'MC Light',
            '20' => 'MC Businesses',
            '30' => 'MC Enterprise',
            '40' => 'Документы ЭДО',
            '50' => 'ВЕТИС Меркурий',
            '60' => 'ЕГАИС Погашение',
            '70' => 'Поставки (Почтовый робот)',
            '80' => 'R-keeper',
            '90' => 'iiko',
            '100' => '1C Интеграция закупщика',
            '110' => '1C Интеграция поставщика',
            '120' => 'Tillypad',
            '130' => 'Poster',
        ];
        foreach ($array as $index => $name) {
            $licence = \common\models\licenses\License::findOne(['name' => $name]);
            $licence->sort_index = $index;
            $licence->save();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181120_072250_add_sort_column_to_license cannot be reverted.\n";

        return false;
    }

}
