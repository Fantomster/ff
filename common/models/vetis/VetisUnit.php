<?php

namespace common\models\vetis;

use Yii;

/**
 * This is the model class for table "vetis_unit".
 *
 * @property string $uuid
 * @property string $guid
 * @property int $last
 * @property int $active
 * @property int $status
 * @property string $next
 * @property string $previous
 * @property string $name
 * @property string $fullName
 * @property string $commonUnitGuid
 * @property int $factor
 * @property string $createDate
 * @property string $updateDate
 * @property object $unit
 */
class VetisUnit extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vetis_unit';
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
            [['last', 'active', 'status', 'factor'], 'integer'],
            [['createDate', 'updateDate'], 'safe'],
            [['uuid', 'guid', 'next', 'previous', 'name', 'fullName', 'commonUnitGuid'], 'string', 'max' => 255],
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
            'fullName' => 'Full Name',
            'commonUnitGuid' => 'Common Unit Guid',
            'factor' => 'Factor',
            'createDate' => 'Create Date',
            'updateDate' => 'Update Date',
        ];
    }
    
    public function getUnit()
    {
        return \yii\helpers\Json::decode($this->data);
    }
    
    public static function getUnitList() {
        $models = self::find()
                ->select(['uuid', 'name'])
                ->where(['active' => true, 'last' => true])
                ->asArray()
                ->all();

        return ArrayHelper::map($models, 'uuid', 'name');
    }
}
