<?php
/**
 * Created by PhpStorm.
 * Developer: Arsen
 * Vk: https://vk.com/a.arsik
 * Inst: https://www.instagram.com/arsen.web/
 * Date: 2019-02-11
 * Time: 18:36
 */

namespace common\models\rbac\helpers;

use api_web\components\Registry;
use common\models\Organization;
use yii\db\Query;

class RbacHelper
{
    /**@var array $statuses */
    static $dictRoles = [
        1  => Registry::ADMINISTRATOR_MIXCART,
        7  => Registry::MANAGER_MIXCART,
        3  => Registry::ADMINISTRATOR_RESTAURANT,
        4  => Registry::MANAGER_RESTAURANT,
        16 => Registry::BOOKER_RESTAURANT,
        17 => Registry::PURCHASER_RESTAURANT,
        18 => Registry::JUNIOR_PURCHASER,
        19 => Registry::PROCUREMENT_INITIATOR,
//            '' => 'BUSINESS_OWNER',
//            '' => 'OPERATOR',
    ];

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