<?php

/**
 * Class Migration
 * @package api_web\classes
 * @createdBy Basil A Konakov
 * @createdAt 2018-10-02
 * @author Mixcart
 * @module WEB-API
 * @version 2.0
 */

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "organization_dictionary".
 *
 * @property int $id Идентификатор записи
 * @property int $outer_dic_id Код словаря
 * @property int $org_id Код организации
 * @property int $status_id ID статуса - выгружен, ошибка, не выгружался
 * @property int $count Количество записей в словаре
 * @property string $created_at Дата создания
 * @property string $updated_at Дата обновления
 *
 * @property Organization $org
 * @property OuterDictionary $outerDic
 */
class OrganizationDictionary extends ActiveRecord
{

    public static function tableName()
    {
        return 'organization_dictionary';
    }

    public static function getDb()
    {
        return Yii::$app->get('db_api');
    }

    public function rules()
    {
        return [
            [['outer_dic_id', 'org_id'], 'required'],
            [['outer_dic_id', 'org_id', 'status_id', 'count'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['org_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::class, 'targetAttribute' => ['org_id' => 'id']],
            [['outer_dic_id'], 'exist', 'skipOnError' => true, 'targetClass' => OuterDictionary::class, 'targetAttribute' => ['outer_dic_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор записи',
            'outer_dic_id' => 'Код словаря',
            'org_id' => 'Код организации',
            'status_id' => 'ID статуса - выгружен, ошибка, не выгружался',
            'count' => 'Количество записей в словаре',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата обновления',
        ];
    }

    public function getOrg()
    {
        return $this->hasOne(Organization::class, ['id' => 'org_id']);
    }

    public function getOuterDic()
    {
        return $this->hasOne(OuterDictionary::class, ['id' => 'outer_dic_id']);
    }


    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => \gmdate('Y-m-d H:i:s'),
            ],
        ];
    }

}
