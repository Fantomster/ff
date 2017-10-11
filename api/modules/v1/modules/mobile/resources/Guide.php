<?php

namespace api\modules\v1\modules\mobile\resources;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class Guide extends \common\models\guides\Guide
{
    public $count;
    public $page;
    
    public function fields()
    {
        return ['id', 'client_id', 'type', 'name', 'deleted', 'created_at', 'updated_at', 'organization_name', 'supp_org_id'];
    }
    
    public function rules()
    {
        return [
            [['client_id', 'type', 'deleted','page','count'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['name'], 'filter', 'filter' => '\yii\helpers\HtmlPurifier::process'],
            [['client_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::className(), 'targetAttribute' => ['client_id' => 'id']],
        ];
    }

}
