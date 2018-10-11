<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "ecom_integration_config".
 *
 * @property int $id
 * @property int $org_id
 * @property string $provider
 * @property string $realization
 */
class EcomIntegrationConfig extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ecom_integration_config';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['org_id'], 'required'],
            [['org_id'], 'integer'],
            [['provider', 'realization'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'org_id' => 'Org ID',
            'provider' => 'Provider',
            'realization' => 'Realization',
        ];
    }
}
