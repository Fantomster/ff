<?php

use yii\db\Migration;

/**
 * Class m190208_083341_organization_contact
 */
class m190208_083341_organization_contact extends Migration
{
    private $table = '{{%organization_contact}}';
    private $tableNotification = '{{%organization_contact_notification}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->table, [
            'id'              => $this->primaryKey(),
            'organization_id' => $this->integer()->notNull()->comment('ID организации'),
            'type_id'         => $this->integer()->notNull()->comment('Тип контакта Email или Телефон'),
            'contact'         => $this->string(50)->notNull()->comment("Телефон или Email"),
            'created_at'      => $this->timestamp()->null(),
            'updated_at'      => $this->timestamp()->null(),
        ]);

        $this->createTable($this->tableNotification, [
            'organization_contact_id' => $this->integer()->notNull()->comment("Связь с таблицей organization_contact"),
            'client_id'               => $this->integer()->notNull()->comment('Связь с рестораном'),
            'order_create'            => $this->tinyInteger(1)->defaultValue(0)->comment("Подписка на создание заказа"),
            'order_canceled'          => $this->tinyInteger(1)->defaultValue(0)->comment("Подписка на отмену заказа"),
            'order_changed'           => $this->tinyInteger(1)->defaultValue(0)->comment("Подписка на изменение заказа"),
            'order_done'              => $this->tinyInteger(1)->defaultValue(0)->comment("Подписка на завершение заказа"),
            'created_at'              => $this->timestamp()->null(),
            'updated_at'              => $this->timestamp()->null(),
        ]);

        $this->createIndex('idx_organization_contact', $this->tableNotification, 'organization_contact_id');
        $this->createIndex('idx_organization_contact_client', $this->tableNotification, ['organization_contact_id', 'client_id']);

        $this->addForeignKey('fk_org_id', $this->table, 'organization_id', 'organization', 'id');
        $this->addForeignKey('fk_organization_contact_id', $this->tableNotification, 'organization_contact_id', $this->table, 'id', 'CASCADE');
        $this->addForeignKey('fk_client_id', $this->tableNotification, 'client_id', 'organization', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->tableNotification);
        $this->dropTable($this->table);
    }
}
