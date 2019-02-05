<?php
/**
 * Created by PhpStorm.
 * Developer: Arsen
 * Vk: https://vk.com/a.arsik
 * Inst: https://www.instagram.com/arsen.web/
 * Date: 2019-02-05
 * Time: 15:27
 */

namespace common\models\rbac;

use Yii;
use yii\rbac\Rule;

class BizRuleModel extends \yii2mod\rbac\models\BizRuleModel
{
    public $nameSpaceRules = "common\\models\\rbac\\rules\\";

    /**
     * Validate className
     */
    public function classExists()
    {
        $this->className = $this->nameSpaceRules . $this->className;
        if (!class_exists($this->className)) {
            $message = Yii::t('yii2mod.rbac', "Unknown class '{class}'", ['class' => $this->className]);
            $this->addError('className', $message);

            return;
        }

        if (!is_subclass_of($this->className, Rule::class)) {
            $message = Yii::t('yii2mod.rbac', "'{class}' must extend from 'yii\\rbac\\Rule' or its child class", [
                'class' => $this->className,]);
            $this->addError('className', $message);
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'name' => 'Название',
            'className' => 'Название класса',
        ];
    }
}