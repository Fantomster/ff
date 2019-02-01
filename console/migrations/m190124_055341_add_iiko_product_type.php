<?php

use yii\db\Migration;

/**
 * Class m190124_055341_add_iiko_product_type
 */
class m190124_055341_add_iiko_product_type extends Migration
{

    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    /**
     * Service ID
     *
     * @var int
     */
    public $service = \api_web\components\Registry::IIKO_SERVICE_ID;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            \common\models\OuterProductType::tableName(),
            'selected',
            $this->tinyInteger()->null()->defaultValue(0)->comment('Тип выбран для загрузки')
        );

        $rows = [
            [$this->service, 'GOODS', 'Товар', 1],
            [$this->service, 'DISH', 'Блюдо', 0],
            [$this->service, 'PREPARED', 'Заготовка(полуфабрикат)', 0],
            [$this->service, 'SERVICE', 'Услуга', 0],
            [$this->service, 'MODIFIER', 'Модификатор', 0],
            [$this->service, 'OUTER', 'Товары поставщиков, не являющиеся товарами систем iiko', 0],
            [$this->service, 'RATE', 'Тариф(дочерний элемента для услуги)', 0],
            [$this->service, 'PETROL', 'Топливо', 0],
        ];

        $this->batchInsert(\common\models\OuterProductType::tableName(), ['service_id', 'value', 'comment', 'selected'], $rows);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(\common\models\OuterProductType::tableName(), 'selected');
        $this->delete(\common\models\OuterProductType::tableName(), ['service_id' => $this->service]);
    }
}
