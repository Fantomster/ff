<?php

use yii\db\Migration;

/**
 * Class m180410_110115_is_work_edit
 */
class m180410_110115_is_work_edit extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn(\common\models\Organization::tableName(), 'is_work');
        $this->addColumn(\common\models\Organization::tableName(), 'is_work', $this->integer()->defaultValue(0));

        $this->update(\common\models\Organization::tableName(), ['is_work' => null], ['type_id' => \common\models\Organization::TYPE_RESTAURANT]);
        $this->update(\common\models\Organization::tableName(), ['is_work' => 1], ['type_id' => \common\models\Organization::TYPE_SUPPLIER]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180410_110115_is_work_edit cannot be reverted.\n";
        return false;
    }
}
