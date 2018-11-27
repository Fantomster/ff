<?php

namespace common\behaviors;

use yii\base\Behavior;
use yii\helpers\Json;
use yii\db\ActiveRecord;
use yii\base\ModelEvent;

/**
 * Description of LogDeletedBehavior
 *
 * @author El Babuino
 */
class LogDeletedBehavior extends Behavior
{
    public $logCategory = "deleted_log";

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete'
        ];
    }
    
    /**
     * @param ModelEvent $event
     */
    public function beforeDelete($event)
    {
        if (!is_a($this->owner, '\yii\db\ActiveRecord')) {
            return;
        }
        if (!is_a(\Yii::$app, 'yii\console\Application')) {
            $user = "console application";
            $location = \Yii::$app->controller->id . '/' . \Yii::$app->controller->action->id;
        } else {
            $user = \Yii::$app->user->isGuest ? 'user unknown' : 'user ' . \Yii::$app->user->id;
            $location = \Yii::app()->request->userHostAddress ?? 'location unknown';
        }
        $attributes = $this->owner->attributes ?? [];
        $message = "Object " . $this->owner::className() . "(" . Json::encode($attributes) . ") was deleted by $user from $location";
        \Yii::info($message, $this->logCategory);
    }
}
