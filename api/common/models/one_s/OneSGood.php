<?php

namespace api\common\models\one_s;

use Yii;
use common\models\Organization;


/**
 * This is the model class for table "one_s_good".
 *
 * @property integer $id
 * @property integer $cid
 * @property string $name
 * @property integer $org_id
 * @property integer $parent_id
 * @property string $measure
 * 
 * 
 */
class OneSGood extends \yii\db\ActiveRecord
{
      
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'one_s_good';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'cid', 'org_id'], 'required'],
            [['org_id', 'is_active'], 'integer'],
            [['name', 'measure'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cid' => 'CID',
            'name' => 'Наименование',
            'measure' => 'Ед.измерения',
            'updated_at' => 'Обновлено',
            'is_active' => 'Показатель активности'
        ];
    }


    public function getOrganization() {
           return $this->hasOne(Organization::className(), ['id' => 'org_id']);
    }
    
    public function getOrganizationName()
    {
        $org = $this->organization;
        return $org ? $org->name : 'no';
    }
    
    
    public static function getDb()
    {
       return \Yii::$app->db_api;
    }


}
