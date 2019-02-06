<?php
/**
 * Created by PhpStorm.
 * Developer: Arsen
 * Vk: https://vk.com/a.arsik
 * Inst: https://www.instagram.com/arsen.web/
 * Date: 2019-02-06
 * Time: 09:46
 */

namespace common\models\rbac\rules;

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

        if (isset(\Yii::$app->authManager->getRolesByUser($user)[$item->name])) {
            return true;
        }

        return false;
    }
}