<?php

/**
 * Class Migration
 *
 * @package   api_web\classes
 * @createdBy Basil A Konakov
 * @createdAt 2018-10-02
 * @author    Mixcart
 * @module    WEB-API
 * @version   2.0
 */

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "outer_dictionary".
 *
 * @property int                      $id         Идентификатор словаря
 * @property string                   $name       Наименование словаря
 * @property int                      $service_id Код сервиса
 * @property OrganizationDictionary[] $organizationDictionaries
 * @property AllService               $service
 */
class OuterDictionary extends ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%outer_dictionary}}';
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
            [['name', 'service_id'], 'required'],
            [['service_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['service_id'], 'exist', 'skipOnError' => true, 'targetClass' => AllService::class, 'targetAttribute' => ['service_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'         => 'Идентификатор словаря',
            'name'       => 'Наименование словаря',
            'service_id' => 'Код сервиса',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganizationDictionaries()
    {
        return $this->hasMany(OrganizationDictionary::class, ['outer_dic_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(AllService::class, ['id' => 'service_id']);
    }
}
