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
class RkServicedata extends \yii\db\ActiveRecord {
    // const STATUS_UNLOCKED = 0;
    // const STATUS_LOCKED = 1;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'rk_service_data';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            //    [['org','fd','td','object_id','status_id'], 'required'],
            //    [['id','fid','org','ver'], 'integer'],
            [['id','service_id','org', 'fd', 'td','status_id'], 'safe'],
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
            'org' => 'MixCart ORG ID',
            'service_id' => 'Код сервиса',
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

    public function getCode() {
        return $this->hasOne(RkService::className(), ['id' => 'service_id'])->one()->code;
    }

    public function getService() {
        return $this->hasOne(RkService::className(), ['id' => 'service_id']);
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


        if (!$insert && ($this->attributes['org'] != $changedAttributes['org'])) {

            if (!$oldic = RkDic::find()->andWhere('org_id = :org', [':org' => $this->org])->all()) {

                $dics = RkDictype::find()->all();

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

        parent::afterSave($insert, $changedAttributes);
    }

    public static function getDb() {
        return \Yii::$app->db_api;
    }

}
