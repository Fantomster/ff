<?php
/**
 * Created by PhpStorm.
 * Developer: Arsen
 * Vk: https://vk.com/a.arsik
 * Inst: https://www.instagram.com/arsen.web/
 * Date: 2019-02-11
 * Time: 18:36
 */

namespace backend\modules\rbac\helpers;

use common\models\Organization;
use yii\db\Query;

class RbacHelper
{
    public static function getOrgByUserId($userId)
    {
        return (new Query())
            ->select([
                'name'
            ])
            ->from(['org' => Organization::tableName()])
            ->innerJoin(['aa' => '{{%auth_assignment}}'], 'aa.organization_id = org.id')
            ->where(['aa.user_id' => $userId])
            ->indexBy('id')
            ->column();
    }
}