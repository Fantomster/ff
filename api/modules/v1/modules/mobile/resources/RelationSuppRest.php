<?php

namespace api\modules\v1\modules\mobile\resources;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class RelationSuppRest extends \common\models\RelationSuppRest
{
    public function fields()
    {
        return ['id', 'rest_org_id', 'supp_org_id', 'cat_id', 'invite', 'created_at', 'updated_at', 
            'status', 'uploaded_catalog', 'uploaded_processed', 'is_from_market'];
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['rest_org_id', 'supp_org_id', 'cat_id'], 'integer'],
            [['uploaded_catalog'], 'file'],
            [['uploaded_processed', 'vendor_manager_id'], 'safe'],
        ];
    }
}