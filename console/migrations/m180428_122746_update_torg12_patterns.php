<?php

use yii\db\Migration;

/**
 * Class m180428_122746_update_torg12_patterns
 */
class m180428_122746_update_torg12_patterns extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->truncateTable('{{%integration_torg12_columns}}');

        $this->execute( "INSERT INTO integration_torg12_columns (id,name,value,regular_expression) VALUES
        (1,'num','№|№№|№ п/п|номер по порядку|Номер по порядку',0)  
       ,(2,'name','название|наименование|наименование, характеристика, сорт, артикул товара|наименование товара (описание выполненных работ, оказанных услуг), имущественного права',0)
       ,(3,'ed','наименование|Единица измерения|ед. изм.|наиме-нование|Единица измерения|условное обозначение (национальное)',0)
       ,(4,'code','код товара/ работ, услуг|код|isbn|ean|артикул|артикул поставщика|код товара поставщика|код (артикул)|штрих-код',0)
       ,(5,'cnt','кол-во|количество|кол-во экз.|общее кол-во|количество (масса нетто)|коли-чество (масса нетто)|количество (объем)',0)
       ,(6,'cnt_place','мест, штук',0)
       ,(7,'not_cnt','в одном месте',0)
       ,(8,'price_without_tax','Цена|цена|цена без ндс|цена без ндс, руб.|цена без ндс руб.|цена без учета ндс|цена без учета ндс, руб.|цена без учета ндс руб.|цена, руб. коп.|цена руб. коп.|цена руб. коп|стоимость товаров (работ, услуг), имущественных прав без налога всего',0)
       ,(9,'price_with_tax','цена с ндс, руб.|цена с ндс руб.|цена, руб.|цена руб.|сумма с учетом ндс, руб. коп.|стоимость товаров (работ, услуг), имущественных прав с налогом всего',0)
       ,(10,'sum_with_tax','сумма.*с.*ндс.*|стоимость.*товаров.*с налогом.*всего',1);");
       $this->execute ("INSERT INTO integration_torg12_columns (id,name,value,regular_expression) VALUES
        (11,'tax_rate','ндс, %|ндс %|ставка ндс, %|ставка ндс %|ставка ндс|ставка, %|ставка %|ндс|налоговая ставка',0)
        ,(12,'total','всего по накладной',0)
        ,(14,'sum_without_tax','сумма.*без.*ндс.*|стоимость.*товаров.*без налога.*всего',1);");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180428_122746_update_torg12_patterns cannot be reverted. But it is OK\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180428_122746_update_torg12_patterns cannot be reverted.\n";

        return false;
    }
    */
}
