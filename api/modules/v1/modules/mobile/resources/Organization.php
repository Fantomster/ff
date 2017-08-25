<?php

namespace api\modules\v1\modules\mobile\resources;
use common\models\OrganizationType;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class Organization extends \common\models\Organization
{
    public $list;
    
    public function fields()
    {
        return ['id', 'name', 'type_id', 'picture'];
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'type_id', 'step', 'es_status', 'rating', 'franchisee_sorted'], 'integer'],
            [['created_at', 'updated_at', 'white_list', 'partnership'], 'safe'],
            [['name', 'city', 'address', 'zip_code', 'phone', 'email', 'website', 'legal_entity', 'contact_name', 'country', 'locality', 'route', 'street_number', 'place_id', 'formatted_address','administrative_area_level_1', 'list'], 'string', 'max' => 255],
            [['name', 'city', 'address', 'zip_code', 'phone', 'website', 'legal_entity', 'contact_name', 'about'], 'filter', 'filter' => '\yii\helpers\HtmlPurifier::process'],
            [['phone'], \borales\extensions\phoneInput\PhoneInputValidator::className()],
            [['email'], 'email'],
            [['lat', 'lng'], 'number'],
            [['type_id'], 'exist', 'skipOnError' => true, 'targetClass' => OrganizationType::className(), 'targetAttribute' => ['type_id' => 'id']],
            [['picture'], 'image', 'extensions' => 'jpg, jpeg, gif, png', 'on' => 'settings'],
        ];
    }
}