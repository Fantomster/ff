<?php
/**
 * Created by PhpStorm.
 * Developer: Arsen
 * Vk: https://vk.com/a.arsik
 * Inst: https://www.instagram.com/arsen.web/
 * Date: 2019-02-05
 * Time: 14:48
 */

namespace backend\modules\rbac\models;

use Yii;
use yii\rbac\Rule;

class AuthItemModel extends \yii2mod\rbac\models\AuthItemModel
{
    public $nameSpaceRules = "backend\\modules\\rbac\\rules\\";

    /**
     * Check for rule
     */
    public function checkRule()
    {
        $this->ruleName = $this->nameSpaceRules . $name = $this->ruleName;

        if (!$this->manager->getRule($this->ruleName)) {
            try {
                $rule = Yii::createObject($this->ruleName);
                if ($rule instanceof Rule) {
                    $rule->name = $this->ruleName;
                    $this->manager->add($rule);
                } else {
                    $this->addError('ruleName', Yii::t('yii2mod.rbac', 'Invalid rule "{value}"', ['value' => $this->ruleName]));
                }
            } catch (\Exception $exc) {
                $this->addError('ruleName', Yii::t('yii2mod.rbac', 'Rule "{value}" does not exists', ['value' => $this->ruleName]));
            }
        }
    }
}