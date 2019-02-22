<?php
/**
 * Created by PhpStorm.
 * Developer: Arsen
 * Vk: https://vk.com/a.arsik
 * Inst: https://www.instagram.com/arsen.web/
 * Date: 2019-02-06
 * Time: 18:17
 */

namespace common\components;

use common\models\rbac\base\Assignment;
use yii\db\Query;

class DbManager extends \yii\rbac\DbManager
{
    protected function addRule($rule)
    {
        $time = \Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
        if ($rule->createdAt === null) {
            $rule->createdAt = $time;
        }
        if ($rule->updatedAt === null) {
            $rule->updatedAt = $time;
        }
        $this->db->createCommand()
            ->insert($this->ruleTable, [
                'name'       => $rule->name,
                'data'       => serialize($rule),
                'created_at' => $rule->createdAt,
                'updated_at' => $rule->updatedAt,
            ])->execute();

        $this->invalidateCache();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function updateRule($name, $rule)
    {
        if ($rule->name !== $name && !$this->supportsCascadeUpdate()) {
            $this->db->createCommand()
                ->update($this->itemTable, ['rule_name' => $rule->name], ['rule_name' => $name])
                ->execute();
        }
        $rule->updatedAt = \Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');;

        $this->db->createCommand()
            ->update($this->ruleTable, [
                'name'       => $rule->name,
                'data'       => serialize($rule),
                'updated_at' => $rule->updatedAt,
            ], [
                'name' => $name,
            ])->execute();

        $this->invalidateCache();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function addItem($item)
    {
        $time = \Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
        if ($item->createdAt === null) {
            $item->createdAt = $time;
        }
        if ($item->updatedAt === null) {
            $item->updatedAt = $time;
        }
        $this->db->createCommand()
            ->insert($this->itemTable, [
                'name'        => $item->name,
                'type'        => $item->type,
                'description' => $item->description,
                'rule_name'   => $item->ruleName,
                'data'        => $item->data === null ? null : serialize($item->data),
                'created_at'  => $item->createdAt,
                'updated_at'  => $item->updatedAt,
            ])->execute();

        $this->invalidateCache();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function updateItem($name, $item)
    {
        if ($item->name !== $name && !$this->supportsCascadeUpdate()) {
            $this->db->createCommand()
                ->update($this->itemChildTable, ['parent' => $item->name], ['parent' => $name])
                ->execute();
            $this->db->createCommand()
                ->update($this->itemChildTable, ['child' => $item->name], ['child' => $name])
                ->execute();
            $this->db->createCommand()
                ->update($this->assignmentTable, ['item_name' => $item->name], ['item_name' => $name])
                ->execute();
        }

        $item->updatedAt = \Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');

        $this->db->createCommand()
            ->update($this->itemTable, [
                'name'        => $item->name,
                'description' => $item->description,
                'rule_name'   => $item->ruleName,
                'data'        => $item->data === null ? null : serialize($item->data),
                'updated_at'  => $item->updatedAt,
            ], [
                'name' => $name,
            ])->execute();

        $this->invalidateCache();

        return true;
    }

    /**
     * {@inheritdoc}
     * @param $role
     * @param $userId
     * @param $orgId
     * @return bool|Assignment
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function assignUserByOrg($role, $userId, $orgId)
    {
        $assignment = new Assignment([
            'userId'    => $userId,
            'roleName'  => $role->name,
            'createdAt' => \Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss'),
            'orgId'     => $orgId
        ]);

        $existsRole = (new Query())
            ->select(['*'])
            ->from('{{%auth_assignment}}')
            ->where([
                'item_name'       => $assignment->roleName,
                'organization_id' => $assignment->orgId,
                'user_id'         => $assignment->userId
            ])
            ->exists();

        if ($existsRole) {
            return true;
        }

        $this->db->createCommand()
            ->insert($this->assignmentTable, [
                'user_id'         => $assignment->userId,
                'item_name'       => $assignment->roleName,
                'created_at'      => $assignment->createdAt,
                'organization_id' => $assignment->orgId
            ])->execute();

        return $assignment;
    }

    /**
     * @param $role
     * @param $userId
     * @param $orgId
     * @return bool
     * @throws \yii\db\Exception
     */
    public function revokeUserByOrg($role, $userId, $orgId)
    {
        if ($this->isEmpty($userId) || $this->isEmpty($orgId)) {
            return false;
        }

        return $this->db->createCommand()
                ->delete($this->assignmentTable, [
                    'user_id'         => (string)$userId,
                    'item_name'       => $role->name,
                    'organization_id' => $orgId
                ])
                ->execute() > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserAssignmentsByOrg($userId, $orgId)
    {
        if ($this->isEmpty($userId)) {
            return [];
        }

        $query = (new Query())
            ->from($this->assignmentTable)
            ->where(['user_id' => (string)$userId, 'organization_id' => $orgId]);

        $assignments = [];
        foreach ($query->all($this->db) as $row) {
            $assignments[$row['item_name']] = new Assignment([
                'userId'    => $row['user_id'],
                'roleName'  => $row['item_name'],
                'createdAt' => $row['created_at'],
                'orgId'     => $row['organization_id'],
            ]);
        }

        return $assignments;
    }

    /**
     * Check whether $userId is empty.
     *
     * @param mixed $attribute
     * @return bool
     */
    private function isEmpty($attribute)
    {
        return !isset($attribute) || $attribute === '';
    }
}