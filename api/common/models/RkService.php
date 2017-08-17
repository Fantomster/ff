<?php

namespace api\common\models;

use Yii;
use common\models\Organization;

/**
 * This is the model class for table "rk_access".
 *
 * @property integer $id
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
class RkService extends \yii\db\ActiveRecord {
    // const STATUS_UNLOCKED = 0;
    // const STATUS_LOCKED = 1;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'rk_service';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            //    [['org','fd','td','object_id','status_id'], 'required'],
            //    [['id','fid','org','ver'], 'integer'],
            [['created_at', 'updated_at', 'is_deleted', 'user_id', 'org', 'fd', 'td', 'status_id', 'is_deleted', 'code', 'name', 'address', 'phone'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'code' => 'ID Объекта',
            'name' => 'Название из R-keeper',
            'fd' => 'Активно с',
            'td' => 'Активно по',
            'status_id' => 'Статус',
            'org' => 'Организация R-keeper',
        ];
    }

    public static function getStatusArray() {
        return [
            RkAccess::STATUS_UNLOCKED => 'Активен',
            RkAccess::STATUS_LOCKED => 'Отключен',
        ];
    }

    public function getOrganization() {
        return $this->hasOne(Organization::className(), ['id' => 'org']);
    }

    public function getOrganizationName() {
        $org = $this->organization;
        return $org ? $org->name : 'no';
    }

    public function beforeSave($insert) {
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

    public function afterSave($insert, $changedAttributes) {
        parent::afterSave($insert, $changedAttributes);

        //  var_dump($changedAttributes);

        if (!$insert && in_array('org', $changedAttributes)) {

            $dics = RkDictype::findAll();

            foreach ($dics as $dic) {
                $model = new RkDic;
                $model->dictype_id = $dic->id;
                $model->dicstatus_id = 1;
                $model->obj_count = 0;
                $model->created_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:i:s');
                $model->org_id = $this->org;

                if (!$model->save()) {
                    print_r($model->getErrors());
                    die();
                }
            }
        }
    }

    public static function getDb() {
        return \Yii::$app->db_api;
    }

}
