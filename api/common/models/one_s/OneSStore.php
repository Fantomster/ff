<?php

namespace api\common\models\one_s;

use Yii;
use common\models\Organization;
use yii\helpers\ArrayHelper;


/**
 * This is the model class for table "one_s_good".
 *
 * @property integer $id
 * @property integer $cid
 * @property string $name
 * @property integer $org_id
 * @property integer $parent_id
 * @property string $address
 *
 *
 */
class OneSStore extends \yii\db\ActiveRecord
{


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'one_s_store';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name','cid','org_id'], 'required'],
            [['org_id', 'is_active'], 'integer'],
            [['name', 'address'], 'string', 'max' => 255],
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
            'address' => 'Адрес',
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

    /**
     * get list of stores
     *
     * @return array
     */
    public function getStores($org_id, $all = true, $notMap=true) {
        $query = OneSStore::find()
            ->select(['id', 'name'])->where(['org_id' => $org_id, 'is_active' => true]);

        if($notMap){
            $stores = ArrayHelper::map($query->orderBy(['name' => SORT_ASC])
                ->asArray()
                ->all(), 'id', 'name');
        }else{
            $stores = $query->orderBy(['name' => SORT_ASC])
                ->asArray()
                ->all();
        }

        if ($all) {
            $stores[''] = '';
        }
        ksort($stores);
        return $stores;
    }
}
