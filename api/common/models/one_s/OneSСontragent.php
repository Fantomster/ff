<?php

namespace api\common\models\one_s;

use Yii;
use common\models\Organization;


/**
 * This is the model class for table "one_s_contragent".
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
class OneSСontragent extends \yii\db\ActiveRecord
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
            [['org_id'], 'integer'],
            [['name', 'inn'], 'string', 'max' => 255],
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
            'inn' => 'ИНН',
            'updated_at' => 'Обновлено',
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
