<?php

use yii\db\Migration;

/**
 * Class m180511_081837_add_inn_organization
 */
class m180511_081837_add_inn_organization extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%organization}}', 'inn', $this->string(15)->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%organization}}', 'inn');
    }
}
