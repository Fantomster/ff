<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "catalog".
 *
 * @property integer $id
 * @property integer $supp_org_id
 * @property string $name
 * @property integer $status
 * @property integer $type
 * @property string $create_at
 * @property string $last_update
 */
class Catalog extends \yii\db\ActiveRecord
{    
	const BASE_CATALOG = 1;
	const CATALOG = 2;
	
    const NON_CATALOG = 0;
    
    const STATUS_ON = 1;
    const STATUS_OFF = 0;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'catalog';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'supp_org_id', 'type'], 'required'],
            [['supp_org_id', 'type', 'status'], 'integer'],
            [['create_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'supp_org_id' => 'Org Supp ID',
            'type' => 'Type',
            'status' => 'Status',
            'create_at' => 'Create Datetime',
        ];
    }
    public static function getNameCatalog($id){
	$catalogName = Catalog::find()
	->where(['id' => $id])->one();  
	return $catalogName;
    }
    public static function GetCatalogs($type)
    {
		$catalog = Catalog::find()
		->select(['id','status','name'])
		->where(['supp_org_id' => \common\models\User::getOrganizationUser(Yii::$app->user->id),'type'=>$type])->all();   
		return $catalog;
    }
}
