<?php

use yii\db\Migration;

/**
 * Class m180328_064644_organization_allow_editing
 */
class m180328_064644_organization_allow_editing extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(\common\models\Organization::tableName(), 'allow_editing', $this->integer()->defaultValue(1));
        //Разрешаем к редактированию только тех поставщиков, которых добавил ресторан
        //и поставщик еще не входил в систему
        $this->update(\common\models\Organization::tableName(), ['allow_editing' => 0], [
            'OR',
            ['step' => \common\models\Organization::STEP_OK],
            ['type_id' => \common\models\Organization::TYPE_RESTAURANT]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(\common\models\Organization::tableName(), 'allow_editing');
    }
}
