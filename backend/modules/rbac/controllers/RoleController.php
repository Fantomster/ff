<?php
/**
 * Created by PhpStorm.
 * Developer: Arsen
 * Vk: https://vk.com/a.arsik
 * Inst: https://www.instagram.com/arsen.web/
 * Date: 2019-02-05
 * Time: 15:11
 */

namespace backend\modules\rbac\controllers;

use backend\modules\rbac\base\ItemController;
use yii\rbac\Item;

class RoleController extends ItemController
{
    /**
     * @var int
     */
    protected $type = Item::TYPE_ROLE;

    /**
     * @var array
     */
    protected $labels = [
        'Item'  => 'Роль',
        'Items' => 'Роли',
    ];
}