<?php

use yii\db\Migration;

/**
 * Class m190206_122006_add_delivery_discount_percent
 */
class m190206_122006_add_delivery_discount_percent extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            \common\models\Delivery::tableName(),
            'delivery_discount_percent',
            $this->integer(3)->after('min_order_price')->defaultValue(0)->comment('Скидка на доставку в процентах')
        );

        $this->addColumn(
            \common\models\RelationSuppRest::tableName(),
            'discount_product',
            $this->integer(7)->after('invite')->defaultValue(0)->comment('Скидка на все продукты (применяется для всех товаров прайса поставщика)')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(
            \common\models\Delivery::tableName(),
            'delivery_discount_percent'
        );

        $this->dropColumn(
            \common\models\RelationSuppRest::tableName(),
            'discount_product'
        );
    }
}
