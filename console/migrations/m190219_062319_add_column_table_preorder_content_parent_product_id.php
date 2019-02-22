<?php

use yii\db\Migration;

/**
 * Class m190219_062319_add_column_table_preorder_content_parent_product_id
 */
class m190219_062319_add_column_table_preorder_content_parent_product_id extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            \common\models\PreorderContent::tableName(),
            'parent_product_id',
            $this->integer()
                ->after('product_id')
                ->defaultValue(null)
                ->comment("Аналог одного из продуктов в предзаказе")
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(\common\models\PreorderContent::tableName(), 'parent_product_id');
    }
}
