<?php

namespace common\components;

use \yii\db\ActiveQuery;
use creocoder\nestedsets\NestedSetsQueryBehavior;

class NestedSetsQuery extends ActiveQuery
{
    public function behaviors() {
        return [
            NestedSetsQueryBehavior::className(),
        ];
    }
}