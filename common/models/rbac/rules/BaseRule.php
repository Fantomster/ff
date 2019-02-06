<?php
/**
 * Created by PhpStorm.
 * Developer: Arsen
 * Vk: https://vk.com/a.arsik
 * Inst: https://www.instagram.com/arsen.web/
 * Date: 2019-02-06
 * Time: 09:44
 */

namespace common\models\rbac\rules;

use yii\rbac\Item;
use yii\rbac\Rule;

class BaseRule extends Rule
{
    public $name = 'BaseRule';

    /**
     * Executes the rule.
     *
     * @param string|int $user   the user ID. This should be either an integer or a string representing
     *                           the unique identifier of a user. See [[\yii\web\User::id]].
     * @param Item       $item   the role or permission that this rule is associated with
     * @param array      $params parameters passed to [[CheckAccessInterface::checkAccess()]].
     * @return bool a value indicating whether the rule permits the auth item it is associated with.
     */
    public function execute($user, $item, $params)
    {
        if (\Yii::$app->user->can('supper_admin')) {
            return 1;
        };

        if (\Yii::$app->user->can($item->name . '_not')) {
            return false;
        };

        return true;
    }
}