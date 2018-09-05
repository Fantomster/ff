<?php

use yii\db\Migration;

/**
 * Class m180903_155216_add_index
 */
class m180903_155216_add_index extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex(\common\models\RelationSuppRest::tableName() . '_rest_supp', \common\models\RelationSuppRest::tableName(), [
            'rest_org_id',
            'supp_org_id'
        ], false);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex(\common\models\RelationSuppRest::tableName() . '_rest_supp', \common\models\RelationSuppRest::tableName());
    }
}
