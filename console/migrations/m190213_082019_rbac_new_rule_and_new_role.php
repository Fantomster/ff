<?php

use api_web\components\Registry;
use yii\db\Migration;
use yii\rbac\Role;

/**
 * Class m190213_082019_rbac_new_rule_and_new_role
 */
class m190213_082019_rbac_new_rule_and_new_role extends Migration
{
    private $roleList = [
        Registry::ADMINISTRATOR_MIXCART,
        Registry::MANAGER_MIXCART,
        Registry::BUSINESS_OWNER,
        Registry::ADMINISTRATOR_RESTAURANT,
        Registry::MANAGER_RESTAURANT,
        Registry::BOOKER_RESTAURANT,
        Registry::PURCHASER_RESTAURANT,
        Registry::JUNIOR_PURCHASER,
        Registry::PROCUREMENT_INITIATOR,
        Registry::OPERATOR
    ];

    /**
     * {@inheritdoc}
     * @throws \yii\base\Exception
     */
    public function safeUp()
    {
        $this->insert('{{%auth_rule}}', [
            'name' => 'UserRule',
            'data' => 'O:33:"common\models\rbac\rules\UserRule":3:{s:4:"name";s:8:"UserRule";s:9:"createdAt";s:19:"2019-02-13 11:21:19";s:9:"updatedAt";s:19:"2019-02-13 11:21:19";}',
        ]);

        $this->insert('{{%auth_item}}', [
            'name'        => 'AUTH_USER',
            'type'        => Role::TYPE_ROLE,
            'description' => 'Авторизованный пользователь',
            'rule_name'   => 'UserRule',
            'data'        => null
        ]);

        foreach ($this->roleList as $role) {
            $manager = Yii::$app->authManager;
            $manager->addChild($manager->getRole($role), $manager->getRole('AUTH_USER'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        foreach ($this->roleList as $role) {
            $this->delete('{{%auth_item_child}}', [
                'parent' => $role,
                'child'  => 'AUTH_USER'
            ]);
        }

        $this->delete('{{%auth_item}}', [
            'name'      => 'AUTH_USER', // Название роли
            'type'      => Role::TYPE_ROLE, // Тип определяющий роль это или разрешение
            'rule_name' => 'UserRule', // Название правила от которого наследуются роли
        ]);

        $this->delete('{{%auth_rule}}', [
            'name' => 'UserRule',
            'data' => 'O:33:"common\models\rbac\rules\UserRule":3:{s:4:"name";s:8:"UserRule";s:9:"createdAt";s:19:"2019-02-13 11:21:19";s:9:"updatedAt";s:19:"2019-02-13 11:21:19";}',
        ]);
    }
}
