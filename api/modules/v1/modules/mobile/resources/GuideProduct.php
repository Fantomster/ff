<?php

namespace api\modules\v1\modules\mobile\resources;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class GuideProduct extends \common\models\guides\GuideProduct
{
    public $list;
    
    public function fields()
    {
        return ['id', 'guide_id', 'cbg_id', 'created_at', 'updated_at'];
    }
    
    public function rules()
    {
        return [
            [['list'], 'string'],
            [['guide_id', 'cbg_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['cbg_id'], 'exist', 'skipOnError' => true, 'targetClass' => CatalogBaseGoods::className(), 'targetAttribute' => ['cbg_id' => 'id']],
            [['guide_id'], 'exist', 'skipOnError' => true, 'targetClass' => Guide::className(), 'targetAttribute' => ['guide_id' => 'id']],
        ];
    }

}
