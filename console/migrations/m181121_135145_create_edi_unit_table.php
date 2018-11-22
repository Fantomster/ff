<?php

use yii\db\Migration;

/**
 * Handles the creation of table `edi_unit`.
 */
class m181121_135145_create_edi_unit_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('edi_unit', [
            'id' => $this->primaryKey(),
            'name' => $this->string(30),
            'unit_code' => $this->string(30),
            'description' => $this->string(150),
        ]);
        $this->addCommentOnColumn('edi_unit', 'name', 'Название в системе MixCart');
        $this->addCommentOnColumn('edi_unit', 'unit_code', 'Код во внешней системе EDI');
        $this->addCommentOnColumn('edi_unit', 'description', 'Описание единицы измерения');
        $columns = ['name', 'unit_code', 'description'];
        $array = [
            ["г", "GRM", "грамм"],
                ["кг", "KGM", "килограмм"],
                ["л", "LTR", "литр"],
                ["мм", "MMT", "миллиметр"],
                ["м2", "MTK", "квадратный метр"],
                ["м3", "MTQ", "кубический метр"],
                ["м", "MTR", "метр"],
                ["мг", "MGM", "миллиграмм"],
                ["мл", "MLT", "миллилитр"],
                ["мм3", "MMQ", "кубический миллиметр"],
                ["шт", "PCE", "штуки"],
                ["кор", "CT", "коробка"],
                ["пач", "BH", "пачка"],
                ["пд", "PF", "поддон"],
                ["упк", "PK", "упаковка"],
                ["бут", "BO", "бутылка"],
                ["кон", "CON", "контейнер"],
                ["кор", "CT", "коробка"],
                ["меш", "BG", "мешок"],
                ["набор", "SET", "набор"],
                ["пак", "PA", "пакет"],
                ["ящ", "CR", "ящик"],
                ["рулон", "RO", "рулон"],
        ];
        $this->batchInsert('{{%edi_unit}}', $columns, $array);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('edi_unit');
    }
}
