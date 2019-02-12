<?php
/**
 * Created by PhpStorm.
 * Developer: Arsen
 * Vk: https://vk.com/a.arsik
 * Inst: https://www.instagram.com/arsen.web/
 * Date: 2019-02-05
 * Time: 13:36
 */

namespace common\models\rbac;

use common\components\DbManager;

class AssignmentModel extends \yii2mod\rbac\models\AssignmentModel
{
    /**
     * Assign a roles and permissions to the user.
     *
     * @param array $items
     * @param int   $orgId
     * @return bool
     * @throws \Exception
     */
    public function assignUserByOrg(array $items, int $orgId): bool
    {
        foreach ($items as $name) {
            $item = $this->manager->getRole($name);
            $item = $item ?: $this->manager->getPermission($name);
            (new DbManager())->assignUserByOrg($item, $this->userId, $orgId);
        }

        return true;
    }

    /**
     * Revokes a roles and permissions from the user.
     *
     * @param array $items
     * @return bool
     * @throws \yii\db\Exception
     */
    public function revokeUserByOrg(array $items, $orgId): bool
    {
        foreach ($items as $name) {
            $item = $this->manager->getRole($name);
            $item = $item ?: $this->manager->getPermission($name);
            (new DbManager())->revokeUserByOrg($item, $this->userId, $orgId);
        }

        return true;
    }

    /**
     * Get all available and assigned roles and permissions
     *
     * @param $orgId
     * @return array
     */
    public function getUserItemsByOrg($orgId): array
    {
        $available = [];
        $assigned = [];

        foreach (array_keys($this->manager->getRoles()) as $name) {
            $available[$name] = 'role';
        }

        foreach (array_keys($this->manager->getPermissions()) as $name) {
            if ($name[0] != '/') {
                $available[$name] = 'permission';
            }
        }

        foreach ((new DbManager())->getUserAssignmentsByOrg($this->userId, $orgId) as $item) {
            $assigned[$item->roleName] = $available[$item->roleName];
        }

        return [
            'available' => $available,
            'assigned'  => $assigned,
        ];
    }
}