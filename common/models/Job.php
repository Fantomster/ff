<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "job".
 *
 * @property integer $id
 * @property string $name_job
 * @property integer $organization_type_id
 *
 */
class Job extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'jobs';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name_job'], 'required'],
            [['name_job'], 'string', 'max' => 50],
            [['organization_type_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name_job' => Yii::t('app', 'Name'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProfilesAtJob()
    {
        return $this->hasMany(Profile::className(), ['job_id' => 'id']);
    }
    
    /**
     * array of all jobs of the postavs
     * 
     * @return array
     */
    public static function getListPostav() {
        $models = Job::find()
                ->select(['id', 'name_job', 'organization_type_id'])
                ->where(['organization_type_id' => 2])
                ->asArray()
                ->all();

        return 
//        ArrayHelper::merge(
//                        [null => null], 
                ArrayHelper::map($models, 'id', 'name_job');
       // );
    }

    /**
     * array of all jobs of the restors
     *
     * @return array
     */
    public static function getListRestor() {
        $models = Job::find()
            ->select(['id', 'name_job', 'organization_type_id'])
            ->where(['organization_type_id' => 1])
            ->asArray()
            ->all();

        return
//        ArrayHelper::merge(
//                        [null => null],
            ArrayHelper::map($models, 'id', 'name_job');
        // );
    }
}
