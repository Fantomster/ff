<?php

namespace api\common\models;

use Yii;
use common\models\Organization;


/**
 * This is the model class for table "rk_access".
 *
 * @property integer $id
 * @property integer $const_id
 * @property integer $fid
 * @property integer $org
 * @property string $login
 * @property string $password
 * @property string $token
 * @property string $lic
 * @property datetime $fd
 * @property datetime $td
 * @property integer $ver
 * @property integer $locked
 * @property string $usereq 
 * @property string $comment
 * @property string $salespoint
 * 
 * 
 */
class RkPconst extends \yii\db\ActiveRecord
{
    

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rk_pconst';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'const_id', 'org'], 'integer'],
            [['value'], 'string', 'max' => 65534],
            [['const_id','org','value','created_at','updated_at'], 'safe'],
            
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'const_id' => 'ID константы',
            'org' => 'Организация',
            'value' => 'Текущее значение',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата обновления',
        ];
    }


    public function getRkDicconst() {

        return RkDicconst::findOne(['id' => $this->const_id]);
    }

    public static function getDb()
    {
       return \Yii::$app->db_api;
    }

    public static function getSettingsColumn($organization)
    {
        $res = self::find()
            ->select('*')
            ->join('LEFT JOIN', 'rk_dicconst', '`rk_dicconst`.`denom` = "column_number_invoice"')
            ->where(['org' => $organization])
            ->andWhere('`rk_pconst`.`const_id` = `rk_dicconst`.`id`')
            ->one();
        if($res)
        {
            return ($res->value == 1)? true:false;
        }

    }


}
