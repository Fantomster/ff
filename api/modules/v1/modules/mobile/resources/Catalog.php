<?php

namespace api\modules\v1\modules\mobile\resources;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class Catalog extends \common\models\Catalog
{
    public function fields()
    {
        return ['id', 'type', 'supp_org_id', 'name', 'status', 'created_at', 'updated_at'];
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['supp_org_id', 'type', 'status'], 'integer'],
            [['created_at','updated_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
            ['type', 'uniqueBaseCatalog'],
        ];
    }
}
