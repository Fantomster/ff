<?php

use yii\db\Migration;

/**
 * Class m180822_084528_add_other_field
 */
class m180822_084528_add_other_field extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(\common\models\CatalogTempContent::tableName(), 'other', $this->string()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(\common\models\CatalogTempContent::tableName(), 'other');
    }
}
