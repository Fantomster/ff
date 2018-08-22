<?php

namespace common\models\vetis;

use Yii;

/**
 * This is the model class for table "vetis_purpose".
 *
 * @property string $uuid
 * @property string $guid
 * @property int $last
 * @property int $active
 * @property int $status
 * @property string $next
 * @property string $previous
 * @property string $name
 * @property string $createDate
 * @property string $updateDate
 * @property object $purpose
 */
class VetisPurpose extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vetis_purpose';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_api');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['uuid', 'guid'], 'required'],
            [['last', 'active', 'status'], 'integer'],
            [['createDate', 'updateDate'], 'safe'],
            [['uuid', 'guid', 'next', 'previous', 'name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'uuid' => 'Uuid',
            'guid' => 'Guid',
            'last' => 'Last',
            'active' => 'Active',
            'status' => 'Status',
            'next' => 'Next',
            'previous' => 'Previous',
            'name' => 'Name',
            'createDate' => 'Create Date',
            'updateDate' => 'Update Date',
        ];
    }
    
    public function getPurpose()
    {
        return \yii\helpers\Json::decode($this->data);
    }
    
    public static function getPurposeList() {
        $models = self::find()
                ->select(['uuid', 'name'])
                ->where(['active' => true, 'last' => true])
                ->asArray()
                ->all();

        return ArrayHelper::map($models, 'uuid', 'name');
    }
}
