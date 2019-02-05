<?php
/**
 * Created by PhpStorm.
 * Developer: Arsen
 * Vk: https://vk.com/a.arsik
 * Inst: https://www.instagram.com/arsen.web/
 * Date: 2019-02-05
 * Time: 14:38
 */

namespace backend\modules\rbac\controllers;

use yii\rbac\Item;
use backend\modules\rbac\base\ItemController;

class PermissionController extends ItemController
{
    /**
     * @var int
     */
    protected $type = Item::TYPE_PERMISSION;

    /**
     * @var array
     */
    protected $labels = [
        'Item'  => 'Permission',
        'Items' => 'Permissions',
    ];
}