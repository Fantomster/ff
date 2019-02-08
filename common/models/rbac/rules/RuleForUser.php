<?php
/**
 * Created by PhpStorm.
 * Developer: Arsen
 * Vk: https://vk.com/a.arsik
 * Inst: https://www.instagram.com/arsen.web/
 * Date: 2019-02-06 * Time: 09:46
 */

namespace common\models\rbac\rules;

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

        // Все роли пользователя (дочернии и свои) без дубликатов
        $roles = array_unique((new Query())
            ->select(['role' => 'aic.child',])
            ->from(['aic' => 'auth_item_child'])
            ->innerJoin(['aa' => 'auth_assignment'], 'aa.item_name = aic.parent')
            ->union((new Query())
                ->select(['role' => 'item_name'])
                ->from('auth_assignment')
                ->where(['user_id' => $user]))
            ->where(['aa.user_id' => $user])
            ->column());

        if (in_array($item->name, $roles)) {
            return true;
        }

        return false;
    }
}
