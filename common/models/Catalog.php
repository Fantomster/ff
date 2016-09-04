<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "catalog".
 *
 * @property integer $id
 * @property string $name
 * @property integer $org_supp_id
 * @property integer $type
 * @property string $create_datetime
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
            [['name', 'org_supp_id', 'type'], 'required'],
            [['org_supp_id', 'type'], 'integer'],
            [['create_datetime'], 'safe'],
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
            'org_supp_id' => 'Org Supp ID',
            'type' => 'Type',
            'status' => 'Status',
            'create_datetime' => 'Create Datetime',
        ];
    }
    public static function getNameCatalog($id){
	$catalogName = Catalog::find()
	->where(['id' => $id])->one();  
	return $catalogName;
    }
    public static function GetCatalogs($typeCat)
    {
		$catalog = Catalog::find()
		->select(['id','org_supp_id','status','name'])
		->where(['org_supp_id' => \common\models\User::getOrganizationUser(Yii::$app->user->id),'type'=>$typeCat])->all();   
		return $catalog;
    }
}
