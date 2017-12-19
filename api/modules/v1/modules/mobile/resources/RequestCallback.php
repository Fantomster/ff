<?php

namespace api\modules\v1\modules\mobile\resources;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class RequestCallback extends \common\models\RequestCallback
{
    public $organization_name;
    public function fields()
    {
        return ['id', 'request_id', 'supp_org_id', 'price', 'comment', 'created_at', 'updated_at', 'organization_name'];
    }
    
   /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['request_id', 'supp_org_id'], 'integer'],
            [['created_at', 'updated_at', 'organization_name'], 'safe'],
            [['price'], 'number', 'numberPattern' => '/^\s*[-+]?[0-9]*[.,]?[0-9]+([eE][-+]?[0-9]+)?\s*$/'],
            [['price'], 'number', 'min' => 0.1],
            [['comment'], 'string', 'max' => 255],
            //[['request_id'], 'exist', 'skipOnError' => true, 'targetClass' => Request::className(), 'targetAttribute' => ['request_id' => 'id']],
        ];
    }
}
