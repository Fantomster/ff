<?php

use common\components\DbManager;
use common\models\RelationUserOrganization;
use common\models\User;
use yii\db\Migration;
use yii\db\Query;

/**
 * Class m190211_151515_mapping_user_roles
 */
class m190211_151515_mapping_user_roles extends Migration
{

    /**
     * @return bool|void
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function safeUp()
    {
        $relUserOrgList = (new Query())
            ->select([
                'ruo.user_id',
                'ruo.organization_id',
                'ruo.role_id',
            ])
            ->from(['ruo' => RelationUserOrganization::tableName()])
            ->innerJoin(['u' => User::tableName()], 'u.id = ruo.user_id')
            ->all();

        $dictRoles = [
            1  => 'ADMINISTRATOR_MIXCART',
            7  => 'MANAGER_MIXCART',
            3  => 'ADMINISTRATOR_RESTAURANT',
            4  => 'MANAGER_RESTAURANT',
            16 => 'BOOKER_RESTAURANT',
            17 => 'PURCHASER_RESTAURANT',
            18 => 'JUNIOR_PURCHASER',
            19 => 'PROCUREMENT_INITIATOR',
//            '' => 'BUSINESS_OWNER',
//            '' => 'OPERATOR',
        ];

        foreach ($relUserOrgList as $ruo) {
            if (isset($dictRoles[$ruo['role_id']])) {

                $userRole = $dictRoles[$ruo['role_id']];
                $userId = (int)$ruo['user_id'];
                $orgId = (int)$ruo['organization_id'];

                $userRole = \Yii::$app->authManager->getRole($userRole);
                (new DbManager())->assignUserByOrg($userRole, $userId, $orgId);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('{{%auth_assignment}}');
    }
}
