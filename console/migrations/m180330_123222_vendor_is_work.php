<?php

use yii\db\Migration;

/**
 * Class m180330_123222_vendor_is_work
 */
class m180330_123222_vendor_is_work extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(\common\models\Organization::tableName(), 'is_work', $this->integer());
        //Разрешаем к редактированию только тех поставщиков, которых добавил ресторан
        //и поставщик еще не входил в систему
        $this->update(\common\models\Organization::tableName(), ['is_work' => 1],[
            'step' => \common\models\Organization::STEP_OK,
            'type_id' => \common\models\Organization::TYPE_SUPPLIER
        ]);

        $this->update(\common\models\Organization::tableName(), ['is_work' => 0],[
            "AND",
            '`step` != :s',
            ['type_id' => \common\models\Organization::TYPE_SUPPLIER]
        ], [':s' => \common\models\Organization::STEP_OK]);

        $this->dropColumn(\common\models\Organization::tableName(), 'allow_editing');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(\common\models\Organization::tableName(), 'is_work');

        $this->addColumn(\common\models\Organization::tableName(), 'allow_editing', $this->integer()->defaultValue(1));
        //Разрешаем к редактированию только тех поставщиков, которых добавил ресторан
        //и поставщик еще не входил в систему
        $this->update(\common\models\Organization::tableName(), ['allow_editing' => 0], [
            'OR',
            ['step' => \common\models\Organization::STEP_OK],
            ['type_id' => \common\models\Organization::TYPE_RESTAURANT]
        ]);
    }
}
