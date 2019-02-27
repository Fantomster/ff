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
use common\models\rbac\helpers\RbacHelper;
use common\models\RelationUserOrganization;
use common\models\User;

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
        /** @var User $user */
        $user = $this->user;
        $reverseRoleList = array_flip(RbacHelper::$dictRoles);
        foreach ($items as $name) {
            $item = $this->manager->getRole($name);
            $item = $item ?: $this->manager->getPermission($name);
            (new DbManager())->assignUserByOrg($item, $this->userId, $orgId);
            $user->createRelationUserOrganization($orgId, $reverseRoleList[$name]);
            $user->role_id = $reverseRoleList[$name];
            $user->save();
        }

        return true;
    }

    /**
     * Revokes a roles and permissions from the user.
     *
     * @param array $items
     * @param       $orgId
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\Exception
     * @throws \yii\db\StaleObjectException
     */
    public function revokeUserByOrg(array $items, $orgId): bool
    {
        foreach ($items as $name) {
            $item = $this->manager->getRole($name);
            $item = $item ?: $this->manager->getPermission($name);
            (new DbManager())->revokeUserByOrg($item, $this->userId, $orgId);
            (RelationUserOrganization::findOne([
                'user_id'         => $this->userId,
                'organization_id' => $orgId
            ]))->delete();
        }

        return true;
    }

    /**
     * Get all available and assigned roles and permissions
     *
     * @param $orgId
     * @return array
     */
    public function getUserItemsByOrg($orgId = null): array
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

        if (!is_null($orgId)) {
            foreach ((new DbManager())->getUserAssignmentsByOrg($this->userId, $orgId) as $item) {
                $assigned[$item->roleName] = $available[$item->roleName];
            }
        }

        return [
            'available' => $available,
            'assigned'  => $assigned,
        ];
    }
}
