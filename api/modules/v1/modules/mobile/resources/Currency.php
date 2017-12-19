<?php

namespace api\modules\v1\modules\mobile\resources;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class Currency extends \common\models\Currency
{
    public $count;
    public $page;
    public $list;
    
    public function fields()
    {
        return ['id', 'text', 'symbol'];
    }
    
    public function rules()
    {
        return [
            [['text', 'symbol'], 'required'],
            [['text', 'symbol'], 'string', 'max' => 255],
        ];
    }

}
