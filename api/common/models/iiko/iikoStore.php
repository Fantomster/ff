<?php

namespace api\common\models\iiko;

use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "iiko_store".
 *
 * @property integer $id
 * @property string $uuid
 * @property integer $org_id
 * @property string $denom
 * @property integer $is_active
 * @property string $store_code
 * @property string $store_type
 * @property string $comment
 * @property string $created_at
 * @property string $updated_at
 */
class iikoStore extends \yii\db\ActiveRecord
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
            [['uuid'], 'string', 'max' => 36],
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

    public function beforeSave($insert)
    {
        if($insert) {
            $this->created_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
        }

        $this->updated_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');

        return parent::beforeSave($insert); // TODO: Change the autogenerated stub
    }
}
