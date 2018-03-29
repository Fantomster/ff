<?php

use yii\db\Migration;

/**
 * Class m180328_122815_torg12_fields
 */
class m180328_122815_torg12_fields extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
    UPDATE integration_torg12_columns SET name = 'num', value = '№|№№|№ п/п|номер по порядку|Номер по порядку', regular_expression = 0 WHERE id = 1;
    UPDATE integration_torg12_columns SET name = 'name', value = 'название|наименование|наименование, характеристика, сорт, артикул товара|наименование товара (описание выполненных работ, оказанных услуг), имущественного права', regular_expression = 0 WHERE id = 2;
    UPDATE integration_torg12_columns SET name = 'ed', value = 'наименование|Единица измерения|ед. изм.|наиме-нование|Единица измерения|условное обозначение (национальное)', regular_expression = 0 WHERE id = 3;
    UPDATE integration_torg12_columns SET name = 'code', value = 'код товара/ работ, услуг|код|isbn|ean|артикул|артикул поставщика|код товара поставщика|код (артикул)|штрих-код', regular_expression = 0 WHERE id = 4;
    UPDATE integration_torg12_columns SET name = 'cnt', value = 'кол-во|количество|кол-во экз.|общее кол-во|количество (масса нетто)|коли-чество (масса нетто)|количество (объем)', regular_expression = 0 WHERE id = 5;
    UPDATE integration_torg12_columns SET name = 'cnt_place', value = 'мест, штук', regular_expression = 0 WHERE id = 6;
    UPDATE integration_torg12_columns SET name = 'not_cnt', value = 'в одном месте', regular_expression = 0 WHERE id = 7;
    UPDATE integration_torg12_columns SET name = 'price_without_tax', value = 'Цена|цена|цена без ндс|цена без ндс, руб.|цена без ндс руб.|цена без учета ндс|цена без учета ндс, руб.|цена без учета ндс руб.|цена, руб. коп.|цена руб. коп.|цена руб. коп|стоимость товаров (работ, услуг), имущественных прав без налога всего', regular_expression = 0 WHERE id = 8;
    UPDATE integration_torg12_columns SET name = 'price_with_tax', value = 'цена с ндс, руб.|цена с ндс руб.|цена, руб.|цена руб.|сумма с учетом ндс, руб. коп.|стоимость товаров (работ, услуг), имущественных прав с налогом всего', regular_expression = 0 WHERE id = 9;
    UPDATE integration_torg12_columns SET name = 'sum_with_tax', value = 'сумма.*с.*ндс.*', regular_expression = 1 WHERE id = 10;
    UPDATE integration_torg12_columns SET name = 'tax_rate', value = 'ндс, %|ндс %|ставка ндс, %|ставка ндс %|ставка ндс|ставка, %|ставка %|ндс|налоговая ставка', regular_expression = 0 WHERE id = 11;
    UPDATE integration_torg12_columns SET name = 'total', value = 'всего по накладной', regular_expression = 0 WHERE id = 12;
SQL;

        Yii::$app->db->createCommand($sql)->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return false;
    }
}
