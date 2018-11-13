<?php

namespace api\common\models;

use Yii;
use common\models\Organization;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "rk_agent".
 *
 * @property integer $id
 * @property integer $acc
 * @property integer $rid
 * @property string $denom
 * @property string $agent_type
 * @property string $created_at
 * @property string $updated_at
 * @property string $comment
 * @property integer $vendor_id
 */
class RkAgent extends \yii\db\ActiveRecord
{

    const STATUS_UNLOCKED = 0;
    const STATUS_LOCKED   = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rk_agent';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['acc', 'rid', 'denom'], 'required'],
            [['acc', 'rid', 'vendor_id'], 'integer'],
            [['comment'], 'string', 'max' => 255],
            [['acc', 'rid', 'denom', 'agent_type', 'updated_at'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'         => 'ID',
            'fid'        => 'FID',
            'rid'        => 'RID Store House',
            'denom'      => Yii::t('app', 'api.common.models.store', ['ru' => 'Наименование Store House']),
            'vendor_id'  => Yii::t('app', 'api.common.models.vendor_id', ['ru' => 'Поставщик MixCart']),
            'updated_at' => Yii::t('app', 'api.common.models.updated', ['ru' => 'Обновлено']),
        ];
    }

    public static function getStatusArray()
    {
        return [
            RkAccess::STATUS_UNLOCKED => Yii::t('app', 'api.common.models.active_two', ['ru' => 'Активен']),
            RkAccess::STATUS_LOCKED   => Yii::t('app', 'api.common.models.off_two', ['ru' => 'Отключен']),
        ];
    }

    public function getVendor()
    {
        return $this->hasOne(Organization::className(), ['id' => 'vendor_id']);
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

    public static function getDb()
    {
        return \Yii::$app->db_api;
    }

    /**
     * get list of agents
     *
     * @return array
     */
    public function getAgents($org_id, $all = true, $notMap = true)
    {
        $query = RkAgent::find()
                        ->select(['rid', 'denom'])->where(['acc' => $org_id]);

        if ($notMap) {
            $agents = ArrayHelper::map($query->orderBy(['denom' => SORT_ASC])
                                    ->asArray()
                                    ->all(), 'rid', 'denom');
        } else {
            $agents = $query->orderBy(['denom' => SORT_ASC])
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
