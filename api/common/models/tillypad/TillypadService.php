<?php

namespace api\common\models\tillypad;

use api\common\models\iiko\iikoDic;
use api\common\models\iiko\iikoDictype;
use api\common\models\iiko\iikoDicconst;
use api\common\models\iiko\iikoPconst;
use Yii;
use common\models\Organization;
use yii\db\Expression;

/**
 * This is the model class for table "tillypad_service".
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
 * @property Organization $organization
 */
class TillypadService extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tillypad_service';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_api');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['org', 'status_id', 'is_deleted', 'user_id'], 'integer'],
            [['fd', 'td', 'created_at', 'updated_at'], 'safe'],
            [['object_id', 'phone'], 'string', 'max' => 45],
            [['code'], 'string', 'max' => 128],
            [['name', 'address'], 'string', 'max' => 255],
            [['org'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::class, 'targetAttribute' => ['org' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'         => 'ID',
            'org'        => 'Организация MixCart',
            'fd'         => 'Активно с',
            'td'         => 'Активно по',
            'status_id'  => 'Статус',
            'is_deleted' => 'Is Deleted',
            'object_id'  => 'Object ID',
            'user_id'    => 'User ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'code'       => 'Code',
            'name'       => 'Название',
            'address'    => 'Address',
            'phone'      => 'Phone',
        ];
    }

    public function getOrganization()
    {
        return $this->hasOne(Organization::className(), ['id' => 'org']);
    }

    public function getOrganizationName()
    {
        return $this->organization ? $this->organization->name : 'no';
    }

    public function beforeSave($insert)
    {
        if ($this->fd) {
            $this->fd = Yii::$app->formatter->asDate($this->fd, 'yyyy-MM-dd');
        }

        if ($this->td) {
            $this->td = Yii::$app->formatter->asDate($this->td, 'yyyy-MM-dd');
        }

        return parent::beforeSave($insert); // TODO: Change the autogenerated stub
    }

    public function afterSave($insert, $changedAttributes)
    {

        if ($insert) {
            if (!iikoDic::find()->andWhere('org_id = :org', [':org' => $this->org])->exists()) {
                $dics = iikoDictype::find()->all();
                foreach ($dics as $dic) {
                    $model = new iikoDic();
                    $model->dictype_id = $dic->id;
                    $model->dicstatus_id = iikoDic::STATUS_NOT_SYNC;
                    $model->obj_count = 0;
                    $model->created_at = new Expression('NOW()');
                    $model->org_id = $this->org;

                    if (!$model->save()) {
                        print_r($model->getErrors());
                        die();
                    }
                }
            }
        }

        parent::afterSave($insert, $changedAttributes); // TODO: Change the autogenerated stub
    }

    /**
     * Лицензия
     *
     * @return TillypadService|array|null|\yii\db\ActiveRecord
     */
    public static function getLicense()
    {
        return self::find()
            //->where(['status_id' => 2])
            ->andWhere('org = :org', ['org' => Yii::$app->user->identity->organization_id])
            //->where('org = :org', ['org' => Yii::$app->user->identity->organization_id])
            //->andOnCondition('td >= NOW()')
            //->andOnCondition('fd <= NOW()')
            ->one();
    }

    public static function getMainOrg($org_id)
    {
        $obDicConstModel = iikoDicconst::findOne(['denom' => 'main_org']);
        $obConstModel = iikoPconst::findOne(['const_id' => $obDicConstModel->id, 'org' => $org_id]);
        return isset($obConstModel->value) ? $obConstModel->value : $org_id;
    }

    public static function getChildOrgsId($org_id)
    {
        $obDicConstModel = iikoDicconst::findOne(['denom' => 'main_org']);
        return iikoPconst::find()->select('org')->where(['const_id' => $obDicConstModel->id, 'value' => $org_id])->column();
    }
}
