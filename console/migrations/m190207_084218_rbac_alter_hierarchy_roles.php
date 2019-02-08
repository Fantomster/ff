<?php

use yii\db\Migration;

/**
 * Class m190207_084218_rbac_alter_hierarchy_roles
 */
class m190207_084218_rbac_alter_hierarchy_roles extends Migration
{
    /**
     * {@inheritdoc}
     * @throws \yii\base\Exception
     */
    public function safeUp()
    {
        $this->delete('{{%auth_item_child}}');
        $this->addChild([
            [
                'parent'        => 'ADMINISTRATOR_MIXCART',
                'children_list' => [
                    'MANAGER_MIXCART',
                    'BUSINESS_OWNER',
                    'ADMINISTRATOR_RESTAURANT',
                    'MANAGER_RESTAURANT',
                    'PURCHASER_RESTAURANT',
                    'JUNIOR_PURCHASER',
                    'PROCUREMENT_INITIATOR',
                    'OPERATOR',
                ]
            ],
            [
                'parent'        => 'MANAGER_MIXCART',
                'children_list' => [
                    'BUSINESS_OWNER',
                    'ADMINISTRATOR_RESTAURANT',
                    'MANAGER_RESTAURANT',
                    'PURCHASER_RESTAURANT',
                    'JUNIOR_PURCHASER',
                    'PROCUREMENT_INITIATOR',
                    'OPERATOR',
                ]
            ],
            [
                'parent'        => 'BUSINESS_OWNER',
                'children_list' => [
                    'ADMINISTRATOR_RESTAURANT',
                    'MANAGER_RESTAURANT',
                    'PURCHASER_RESTAURANT',
                    'JUNIOR_PURCHASER',
                    'PROCUREMENT_INITIATOR',
                    'OPERATOR',
                ]
            ],
            [
                'parent'        => 'ADMINISTRATOR_RESTAURANT',
                'children_list' => [
                    'MANAGER_RESTAURANT',
                    'PURCHASER_RESTAURANT',
                    'JUNIOR_PURCHASER',
                    'PROCUREMENT_INITIATOR',
                    'OPERATOR',
                ]
            ],
            [
                'parent'        => 'MANAGER_RESTAURANT',
                'children_list' => [
                    'PURCHASER_RESTAURANT',
                    'JUNIOR_PURCHASER',
                    'PROCUREMENT_INITIATOR',
                    'OPERATOR',
                ]
            ],
            [
                'parent'        => 'PURCHASER_RESTAURANT',
                'children_list' => [
                    'JUNIOR_PURCHASER',
                    'PROCUREMENT_INITIATOR',
                    'OPERATOR',
                ]
            ],
            [
                'parent'        => 'JUNIOR_PURCHASER',
                'children_list' => [
                    'PROCUREMENT_INITIATOR',
                    'OPERATOR',
                ]
            ],
            [
                'parent'        => 'PROCUREMENT_INITIATOR',
                'children_list' => [
                    'OPERATOR',
                ]
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('{{%auth_item_child}}');
    }

    /**
     * @param $hierarchyRoleList
     * @throws \yii\base\Exception
     */
    private function addChild($hierarchyRoleList): void
    {
        $manager = Yii::$app->authManager;
        foreach ($hierarchyRoleList as $role) {
            foreach ($role['children_list'] as $child) {
                $manager->addChild($manager->getRole($role['parent']), $manager->getRole($child));
            }
        }
    }
}
