<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "jobs".
 *
 * @property int       $id                   Идентификатор записи в таблице
 * @property string    $name_job             Наименование должности сотрудников поставщиков и ресторанов
 * @property int       $organization_type_id Идентификатор типа организации, к которой относится должность сотрудников
 * @property Profile[] $profilesAtJob
 */
class Job extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%jobs}}';
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'       => 'ID',
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
    public static function getListPostav()
    {
        $models = Job::find()
            ->select(['id', 'name_job', 'organization_type_id'])
            ->where(['organization_type_id' => 2])
            ->asArray()
            ->all();
        $models[] = ['id' => '0', 'name_allow' => 'Не указано'];
        return ArrayHelper::map($models, 'id', 'name_job');
    }

    /**
     * array of all jobs of the restors
     *
     * @return array
     */
    public static function getListRestor()
    {
        $models = Job::find()
            ->select(['id', 'name_job', 'organization_type_id'])
            ->where(['organization_type_id' => 1])
            ->asArray()
            ->all();
        $models[] = ['id' => '0', 'name_allow' => 'Не указано'];
        return ArrayHelper::map($models, 'id', 'name_job');
    }

    /**
     * @inheritdoc
     */
    public function getJobById($id)
    {
        $query = Job::find()
            ->select(['name_job'])
            ->where(['id' => $id])
            ->asArray()
            ->one();
        return $query;
    }
}
