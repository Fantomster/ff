<?php
/**
 * Created by PhpStorm.
 * Developer: Arsen
 * Vk: https://vk.com/a.arsik
 * Inst: https://www.instagram.com/arsen.web/
 * Date: 2019-02-06 * Time: 09:46
 */

namespace common\models\rbac\rules;

use common\models\User;
use yii\db\Query;

class RuleForUser extends BaseRule
{
    public function execute($user, $item, $params)
    {
        $parent = parent::execute($user, $item, $params);

        if ($parent === 1) {
            return true;
        }

        if ($parent == false) {
            return false;
        }

        $orgId = null;

        if (isset($params['user'])) {
            /** @var User $authorizedUser */
            $authorizedUser = $params['user'];
            $orgId = $authorizedUser->organization->id;
        }

        // Все роли пользователя (дочернии и свои) без дубликатов
        $roles = (new Query())
            ->select(['role' => 'aic.child',])
            ->from(['aic' => 'auth_item_child'])
            ->innerJoin(['aa' => 'auth_assignment'], 'aa.item_name = aic.parent')
            ->union((new Query())
                ->select(['role' => 'item_name'])
                ->from('auth_assignment')
                ->where(['user_id' => $user])
                ->andFilterWhere(['organization_id' => $orgId])
            )
            ->where(['aa.user_id' => $user])
            ->andFilterWhere(['organization_id' => $orgId])
            ->column();

        if (in_array($item->name, $roles)) {
            return true;
        }

        return false;
    }
}
