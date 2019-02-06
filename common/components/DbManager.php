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
}