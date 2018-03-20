<?php

use yii\db\Migration;

/**
 * Class m180302_102452_integration_torg12_columns
 */
class m180302_102452_integration_torg12_columns extends Migration
{
    public $tableName = 'integration_torg12_columns';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'value' => $this->text(),
            'regular_expression' => $this->integer()->defaultValue(0)
        ]);

        $rows = [
            [
                'num',
                '(№|№№|№ п/п|номер по порядку|Номер по порядку|но.*мер.*по.*по.*ряд.*ку)',
                1
            ],
            [
                'name',
                ['название', 'наименование', 'наименование, характеристика, сорт, артикул товара'],
                0
            ],
            [
                'ed',
                ['наименование', 'Единица измерения', 'ед. изм.', 'наиме-нование'],
                0
            ],
            [
                'code',
                ['код', 'isbn', 'ean', 'артикул', 'артикул поставщика', 'код товара поставщика', 'код (артикул)', 'штрих-код'],
                0
            ],
            [
                'cnt',
                ['кол-во', 'количество', 'кол-во экз.', 'общее кол-во', 'количество (масса нетто)', 'коли-чество (масса нетто)'],
                0
            ],
            [
                'cnt_place',
                'мест, штук',
                0
            ],
            [
                'not_cnt',
                'в одном месте',
                0
            ],
            [
                'price_without_tax',
                ['Цена', 'цена', 'цена без ндс', 'цена без ндс, руб.', 'цена без ндс руб.', 'цена без учета ндс', 'цена без учета ндс, руб.', 'цена без учета ндс руб.', 'цена, руб. коп.', 'цена руб. коп.'],
                0
            ],
            [
                'price_with_tax',
                ['цена с ндс, руб.', 'цена с ндс руб.', 'цена, руб.', 'цена руб.', 'сумма с учетом ндс, руб. коп.'],
                0
            ],
            [
                'sum_with_tax',
                'сумма.*с.*ндс.*',
                1
            ],
            [
                'tax_rate',
                ['ндс, %', 'ндс %', 'ставка ндс, %', 'ставка ндс %', 'ставка ндс', 'ставка, %', 'ставка %', 'ндс'],
                0
            ],
            [
                'total',
                'всего по накладной',
                0
            ]
        ];

        foreach($rows as &$row) {
            if($row[2] == 0) {
                if(!is_array($row[1])) {
                    $row[1] = [$row[1]];
                }
                $row[1] = implode('|', $row[1]);
            }
        }

        $this->batchInsert($this->tableName, ['name', 'value', 'regular_expression'], $rows);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
