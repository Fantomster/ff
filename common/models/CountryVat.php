<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "country_vat".
 *
 * @property int    $id
 * @property string $uuid
 * @property string $vats
 * @property string $created_at
 * @property string $updated_at
 * @property int    $created_by_id
 * @property int    $updated_by_id
 */
class CountryVat extends \yii\db\ActiveRecord
{

    public static function getDb()
    {
        return \Yii::$app->db_api;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'country_vat';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uuid', 'vats'], 'required'],
            [['id', 'uuid', 'created_by_id', 'updated_by_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['uuid', 'vats'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'            => Yii::t('app', 'api.common.models.id', ['ru' => 'Идентификатор записи в таблице']),
            'uuid'          => Yii::t('app', 'api.common.models.country.uuid', ['ru' => 'Идентификатор государства']),
            'vats'          => Yii::t('app', 'api.common.models.vats', ['ru' => 'Величины налога']),
            'created_at'    => Yii::t('app', 'api.common.models.created.at', ['ru' => 'Дата и время создания записи в таблице']),
            'updated_at'    => Yii::t('app', 'api.common.models.updated.at', ['ru' => 'Дата и время последнего изменения записи в таблице']),
            'created_by_id' => Yii::t('app', 'api.common.models.created.by.id', ['ru' => 'Идентификатор пользователя, создавшего запись']),
            'updated_by_id' => Yii::t('app', 'api.common.models.updated.by.id', ['ru' => 'Идентификатор пользователя, последним изменившим запись']),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class'              => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value'              => \gmdate('Y-m-d H:i:s'),
            ],
        ];
    }

}
