<?php

namespace api\common\models\one_s;

use Yii;
use common\models\Organization;
use yii\helpers\ArrayHelper;


/**
 * This is the model class for table "one_s_contragent".
 *
 * @property integer $id
 * @property integer $cid
 * @property string $name
 * @property integer $org_id
 * @property integer $parent_id
 * @property string $address
 * @property integer $vendor_id
 *
 *
 */
class OneSContragent extends \yii\db\ActiveRecord
{


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'one_s_contragent';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name','cid','org_id'], 'required'],
            [['org_id','vendor_id', 'is_active'], 'integer'],
            [['name', 'inn_kpp'], 'string', 'max' => 255],
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
            'inn_kpp' => 'ИНН/КПП',
            'updated_at' => 'Обновлено',
            'vendor_id' => 'Поставщик MixCart',
            'is_active' => 'Показатель активности'
        ];
    }



    public function getOrganization() {
        return $this->hasOne(Organization::className(), ['id' => 'org_id']);
    }

    public function getVendor()
    {
        return $this->hasOne(Organization::className(), ['id' => 'vendor_id']);
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

    /**
     * get list of agents
     *
     * @return array
     */
    public function getAgents($org_id, $all = true, $notMap=true) {
        $query = OneSContragent::find()
            ->select(['id', 'name'])->where(['org_id' => $org_id, 'is_active' => true]);

        if($notMap){
            $agents = ArrayHelper::map($query->orderBy(['name' => SORT_ASC])
                ->asArray()
                ->all(), 'id', 'name');
        }else{
            $agents = $query->orderBy(['name' => SORT_ASC])
                ->asArray()
                ->all();
        }

        if ($all) {
            $agents[''] = '';
        }
        ksort($agents);
        return $agents;
    }
}
