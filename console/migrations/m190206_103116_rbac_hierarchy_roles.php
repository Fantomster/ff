<?php

use yii\db\Migration;
use yii\rbac\Role;

class m190206_103116_rbac_hierarchy_roles extends Migration
{
    /**
     * {@inheritdoc}
     * @throws \yii\base\Exception
     */
    public function safeUp()
    {
        Yii::$app->authManager->removeAll();

        $this->alterColumn('{{%auth_item}}', 'created_at', $this->timestamp()->defaultValue((new \yii\db\Expression('NOW()'))));
        $this->alterColumn('{{%auth_item}}', 'updated_at', $this->timestamp()->null());

        $this->alterColumn('{{%auth_rule}}', 'created_at', $this->timestamp()->defaultValue((new \yii\db\Expression('NOW()'))));
        $this->alterColumn('{{%auth_rule}}', 'updated_at', $this->timestamp()->null());

        $this->insert('{{%auth_rule}}', [
            'name' => 'RuleForUser',
            'data' => 'O:36:"common\models\rbac\rules\RuleForUser":3:{s:4:"name";s:11:"RuleForUser";s:9:"createdAt";i:1549457114;s:9:"updatedAt";i:1549457114;}',
        ]);

        $this->batchInsert('{{%auth_item}}', [
            'name',
            'type',
            'description',
            'rule_name',
            'data',
        ], [
            ['ADMINISTRATOR_MIXCART', Role::TYPE_ROLE, 'Администратор MixCart', 'RuleForUser', null],
            ['MANAGER_MIXCART', Role::TYPE_ROLE, 'Менеджер MixCart', 'RuleForUser', null],
            ['BUSINESS_OWNER', Role::TYPE_ROLE, 'Владелец бизнеса', 'RuleForUser', null],
            ['ADMINISTRATOR_RESTAURANT', Role::TYPE_ROLE, 'Управляющий ресторана', 'RuleForUser', null],
            ['MANAGER_RESTAURANT', Role::TYPE_ROLE, 'Менеджер ресторана', 'RuleForUser', null],
            ['BOOKER_RESTAURANT', Role::TYPE_ROLE, 'Бухгалтер ресторана', 'RuleForUser', null],
            ['PURCHASER_RESTAURANT', Role::TYPE_ROLE, 'Закупщик ресторана', 'RuleForUser', null],
            ['JUNIOR_PURCHASER', Role::TYPE_ROLE, 'Младший закупщик', 'RuleForUser', null],
            ['PROCUREMENT_INITIATOR', Role::TYPE_ROLE, 'Инициатор закупки', 'RuleForUser', null],
            ['OPERATOR', Role::TYPE_ROLE, 'Оператор', 'RuleForUser', null],
        ]);

        $this->addChild('PROCUREMENT_INITIATOR', 'OPERATOR');
        $this->addChild('JUNIOR_PURCHASER', 'PROCUREMENT_INITIATOR');
        $this->addChild('PURCHASER_RESTAURANT', 'JUNIOR_PURCHASER');
        $this->addChild('MANAGER_RESTAURANT', 'PURCHASER_RESTAURANT');
        $this->addChild('ADMINISTRATOR_RESTAURANT', 'MANAGER_RESTAURANT');
        $this->addChild('BUSINESS_OWNER', 'ADMINISTRATOR_RESTAURANT');
        $this->addChild('MANAGER_MIXCART', 'BUSINESS_OWNER');
        $this->addChild('ADMINISTRATOR_MIXCART', 'MANAGER_MIXCART');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('{{%auth_rule}}');
        $this->delete('{{%auth_item_child}}');
        $this->delete('{{%auth_item}}');

        $this->alterColumn('{{%auth_item}}', 'created_at', $this->integer(11));
        $this->alterColumn('{{%auth_item}}', 'updated_at', $this->integer(11));

        $this->alterColumn('{{%auth_rule}}', 'created_at', $this->integer(11));
        $this->alterColumn('{{%auth_rule}}', 'updated_at', $this->integer(11));
    }

    /**
     * @param string $parent
     * @param string $child
     * @throws \yii\base\Exception
     */
    private function addChild(string $parent, string $child): void
    {
        $manager = Yii::$app->authManager;
        $manager->addChild($manager->getRole($parent), $manager->getRole($child));
    }
}
