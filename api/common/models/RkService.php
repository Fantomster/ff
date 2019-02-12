<?php

namespace api\common\models;

use Yii;
use common\models\Organization;

/**
 * This is the model class for table "rk_access".
 *
 * @property integer $id
 * @property integer $org
 * @property string  $fd
 * @property string  $td
 * @property integer $status_id
 * @property integer $is_deleted
 * @property string  $object_id
 * @property integer $user_id
 * @property string  $created_at
 * @property string  $updated_at
 * @property string  $code
 * @property string  $name
 * @property string  $address
 * @property string  $phone
 * @property string  $last_active
 */
class RkService extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rk_service';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            //    [['org','fd','td','object_id','status_id'], 'required'],
            //    [['id','fid','org','ver'], 'integer'],
            [['id', 'org', 'fd', 'td', 'status_id', 'is_deleted', 'object_id', 'user_id', 'created_at', 'updated_at', 'code', 'name', 'address', 'phone', 'last_active',], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'        => 'ID',
            'code'      => 'ID Объекта',
            'name'      => 'Название из R-keeper',
            'fd'        => 'Активно с',
            'td'        => 'Активно по',
            'status_id' => 'Статус',
            'org'       => 'Организация R-keeper',
        ];
    }

    public static function getStatusArray()
    {
        return [
            RkAccess::STATUS_UNLOCKED => 'Активен',
            RkAccess::STATUS_LOCKED   => 'Отключен',
        ];
    }

    public function getOrganization()
    {
        return $this->hasOne(Organization::className(), ['id' => 'org']);
    }

    public function getOrganizationName()
    {
        $org = $this->organization;
        return $org ? $org->name : 'no';
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {

            if ($this->fd) {
                $this->fd = Yii::$app->formatter->asDate($this->fd, 'yyyy-MM-dd');
            } else {

            }

            if ($this->td) {
                $this->td = Yii::$app->formatter->asDate($this->td, 'yyyy-MM-dd');
            } else {

            }

            return true;
        }
    }

    public static function getDb()
    {
        return \Yii::$app->db_api;
    }

}
