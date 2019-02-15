<?php

namespace api\common\models;

use Yii;
use common\models\Organization;
use api\common\models\RkService;

/**
 * This is the model class for table "rk_access".
 *
 * @property integer $id
 * @property integer $fid
 * @property integer $org
 * @property string  $login
 * @property string  $password
 * @property string  $token
 * @property string  $lic
 * @property string  $fd
 * @property string  $td
 * @property integer $ver
 * @property integer $locked
 * @property string  $usereq
 * @property string  $comment
 * @property string  $salespoint
 * @property string  $status_id
 * @property string  $service_id
 */
class RkServicedata extends \yii\db\ActiveRecord
{
    // const STATUS_UNLOCKED = 0;
    // const STATUS_LOCKED = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rk_service_data';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            //    [['org','fd','td','object_id','status_id'], 'required'],
            //    [['id','fid','org','ver'], 'integer'],
            [['id', 'service_id', 'org', 'fd', 'td', 'status_id'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'         => 'ID',
            'code'       => 'ID Объекта',
            'name'       => 'Название из R-keeper',
            'fd'         => 'Активно с',
            'td'         => 'Активно по',
            'status_id'  => 'Статус',
            'org'        => 'MixCart ORG ID',
            'service_id' => 'Код сервиса',
        ];
    }

    public static function getStatusArray()
    {
        return [
            RkAccess::STATUS_UNLOCKED => 'Активен',
            RkAccess::STATUS_LOCKED   => 'Отключён',
        ];
    }

    public function getOrganization()
    {
        return $this->hasOne(Organization::className(), ['id' => 'org']);
    }

    public function getCode()
    {
        return $this->hasOne(RkService::className(), ['id' => 'service_id'])->one()->code;
    }

    public function getService()
    {
        return $this->hasOne(RkService::className(), ['id' => 'service_id']);
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

    public function afterSave($insert, $changedAttributes)
    {

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
                    \Yii::error('Не удалось сохранить запись в таблице rk_dic');
                    \Yii::error($model->getErrors());
                }
            }
        }
        parent::afterSave($insert, $changedAttributes);
    }

    public static function getDb()
    {
        return \Yii::$app->db_api;
    }

    /**
     * Лизензия
     *
     * @return iikoService|array|null|\yii\db\ActiveRecord
     */
    public static function getLicense($org_id)
    {
        return self::find()
            //->where(['status_id' => 2])
            ->andWhere('org = :org and status_id = 1', ['org' => $org_id])
            //->andOnCondition('td >= NOW()')
            //->andOnCondition('fd <= NOW()')
            ->one();
    }

    public function getLicenseUcs($org)
    {
        // $query = new Query;
        // $query->select('td,status_id,is_deleted,code')->from('db_api.rk_service')->where('id=:id',['id' => $org]);
        // $rows = Yii::$app->db_api->createCommand($query)->queryOne();
        //   $rows = Yii::$app->db_api->$query->one();
        $ret = RkService::find()->andWhere('id=:id', [':id' => $org])->one();
        // $ret[] = $rows;
        return $ret;
    }

}
