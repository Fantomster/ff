<?php

use yii\db\Migration;

/**
 * Class m181102_130451_fill_edi_provider_table
 */
class m181102_130451_fill_edi_provider_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->batchInsert('{{%edi_provider}}', ['name', 'legal_name', 'web_site'],
                [
                    ['Ecom', 'Ecom', 'https://www.e-vo.ru/'],
                    ['Korus', 'Korus', 'https://edi2.esphere.ru/'],
                    ['Leradata', 'Leradata', 'https://leradata.pro/'],
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
    }
}
