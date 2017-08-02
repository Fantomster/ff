<?php

namespace api\modules\v1\modules\mobile\resources;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class MpCategory extends \common\models\MpCategory
{
    public function fields()
    {
        return ['id', 'parent', 'name'];
    }
    
    public function rules()
    {
        return [
            [['parent'], 'integer'],
            [['name'], 'string', 'max' => 255],
        ];
    }
}
