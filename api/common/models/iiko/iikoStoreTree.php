<?php

namespace api\common\models\iiko;

use Yii;

/**
 * This is the model class for table "iiko_store".
 *
 * @property integer $id
 * @property string $uuid
 * @property string $agent_uuid
 * @property integer $org_id
 * @property string $denom
 * @property integer $is_active
 * @property string $store_code
 * @property string $store_type
 * @property string $comment
 * @property string $created_at
 * @property string $updated_at
 */
class iikoStoreTree extends \kartik\tree\models\Tree
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'iiko_store';
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
            [['uuid', 'org_id'], 'required'],
            [['org_id', 'is_active'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['uuid', 'agent_uuid'], 'string', 'max' => 36],
            [['denom', 'comment'], 'string', 'max' => 250],
            [['store_code'], 'string', 'max' => 50],
            [['store_type'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'uuid' => Yii::t('app', 'Uuid'),
            'agent_uuid' => Yii::t('app', 'Agent Uuid'),
            'org_id' => Yii::t('app', 'Org ID'),
            'denom' => Yii::t('app', 'Denom'),
            'is_active' => Yii::t('app', 'Is Active'),
            'store_code' => Yii::t('app', 'Store Code'),
            'store_type' => Yii::t('app', 'Store Type'),
            'comment' => Yii::t('app', 'Comment'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}
