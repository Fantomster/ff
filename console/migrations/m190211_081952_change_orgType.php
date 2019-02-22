<?php

use yii\db\Migration;

/**
 * Class m190211_081952_change_orgType
 */
class m190211_081952_change_orgType extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('fk-tarif-organization_type_id', 'payment_tarif');
        $this->dropForeignKey('type', 'organization');
        $this->truncateTable('organization_type');

        $this->batchInsert('organization_type', ['name'], [
            ['Ресторан'],
            ['Подключенный поставщик'],
            ['Франчайзи'],
            ['Поставщик']
        ]);

        $this->addForeignKey('fk_type', 'organization', 'type_id', 'organization_type', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190211_081952_change_orgType cannot be reverted.\n";
        return false;
    }
}
