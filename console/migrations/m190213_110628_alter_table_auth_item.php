<?php

use yii\db\Migration;

/**
 * Class m190213_110628_alter_table_auth_item
 */
class m190213_110628_alter_table_auth_item extends Migration
{
    private $authItemChildTable = '{{%auth_item_child}}';
    private $authItemTable = '{{%auth_item}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('auth_item_child_ibfk_1', $this->authItemChildTable);
        $this->dropForeignKey('auth_item_child_ibfk_2', $this->authItemChildTable);
        $this->dropPrimaryKey('name', $this->authItemTable);
        $this->addColumn($this->authItemTable, 'id', $this->primaryKey()->first());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn($this->authItemTable, 'id');
        $this->addPrimaryKey('', $this->authItemTable, 'name');
        $this->addForeignKey(
            'auth_item_child_ibfk_1',
            $this->authItemChildTable,
            'parent',
            $this->authItemTable,
            'name',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'auth_item_child_ibfk_2',
            $this->authItemChildTable,
            'child',
            $this->authItemTable,
            'name',
            'CASCADE',
            'CASCADE'
        );
    }
}
