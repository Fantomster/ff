<?php
/**
 * Created by PhpStorm.
 * Developer: Arsen
 * Vk: https://vk.com/a.arsik
 * Inst: https://www.instagram.com/arsen.web/
 * Date: 2019-02-11
 * Time: 13:43
 */

namespace common\models\rbac\base;

use yii\base\BaseObject;

class Assignment extends BaseObject
{
    /**
     * @var string|int user ID (see [[\yii\web\User::id]])
     */
    public $userId;
    /**
     * @var string the role name
     */
    public $roleName;
    /**
     * @var string timestamp representing the assignment creation time
     */
    public $createdAt;
    /**
     * @var int organization id
     */
    public $orgId;
}