<?php

namespace api\modules\v1\modules\mobile\resources;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class GoodsNotes extends \common\models\GoodsNotes
{
    public function fields()
    {
        return ['id', 'rest_org_id', 'note', 'created_at', 'updated_at', 'catalog_base_goods_id'];
    }
    
   
     public function rules()
    {
        return [
            [['rest_org_id', 'catalog_base_goods_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['note'], 'string', 'max' => 500],
        ];
    }
}
