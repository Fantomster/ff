<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "mp_country".
 *
 * @property integer $id
 * @property string $name
 * @property string $full_name
 * @property string $en_name
 * @property string $alpha2
 * @property string $alpha3
 * @property string $location
 */
class MpCountry extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'mp_country';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'full_name', 'en_name', 'alpha2', 'alpha3', 'location'], 'required'],
            [['name', 'location'], 'string', 'max' => 100],
            [['full_name', 'en_name'], 'string', 'max' => 150],
            [['alpha2'], 'string', 'max' => 2],
            [['alpha3'], 'string', 'max' => 3],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => Yii::t('app', 'common.models.country_three', ['ru'=>'Страна']),
            'full_name' => 'Full Name',
            'en_name' => 'En Name',
            'alpha2' => 'Alpha2',
            'alpha3' => 'Alpha3',
            'location' => 'Location',
        ];
    }
    
    public function ajaxsearch($q){
        $query = self::find();
        $query->select(['name','id']);
        if($q!='*'){
            $query->andFilterWhere(['like', 'name', $q]);
        }

        $query->orderBy('name');
        $res=$query->all();
        $result=[];
        if(!empty($res)){
            foreach($res as $row){
                /**@var countrys $row **/
                $result[]=['id'=>$row->id,'name'=>$row->name];
            }
        }
        $out=['more'=>false,'results'=>$result];
        return $out;
    }
}
