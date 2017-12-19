<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "delivery_regions".
 *
 * @property integer $id
 * @property integer $supplier_id
 * @property string $country
 * @property string $locality
 * @property string $administrative_area_level_1
 * @property integer $exception
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Organization $supplier
 */
class DeliveryRegions extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'delivery_regions';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['supplier_id', 'country'], 'required'],
            [['supplier_id', 'exception'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['country', 'locality', 'administrative_area_level_1'], 'string', 'max' => 255],
            [['supplier_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::className(), 'targetAttribute' => ['supplier_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'supplier_id' => Yii::t('app', 'Supplier ID'),
            'country' => Yii::t('app', 'common.models.country', ['ru'=>'Страна']),
            'locality' => Yii::t('app', 'common.models.city', ['ru'=>'Город']),
            'exception' => Yii::t('app', 'common.models.exception', ['ru'=>'Исключение']),
            'administrative_area_level_1' => Yii::t('app', 'common.models.region', ['ru'=>'Область']),
            'created_at' => Yii::t('app', 'common.models.created_two', ['ru'=>'Создано']),
            'updated_at' => Yii::t('app', 'common.models.refreshed_two', ['ru'=>'Обновлено']),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSupplier()
    {
        return $this->hasOne(Organization::className(), ['id' => 'supplier_id']);
    }

    /**
     * Вернет [id] организаций, с доступной доставкой в этом регионе
     * @return array
     */
    public static function getSuppRegion()
    {
        $tableName = static::tableName();
        $sql = "SELECT supplier_id
                FROM $tableName
                WHERE ( locality = :locality
                       OR ( administrative_area_level_1 = :region
                           AND length(locality) < 1 ) )
                  AND
                  exception = :exception";

        $command = Yii::$app->db->createCommand($sql)
            ->bindValue(':locality', Yii::$app->request->cookies->get('locality'))
            ->bindValue(':region', Yii::$app->request->cookies->get('region'));

        $supplierRegion = $command->bindValue(':exception', 0)->queryColumn();
        $exclude_region = $command->bindValue(':exception', 1)->queryColumn();

        if (!empty($supplierRegion)) {
            if (!empty($exclude_region)) {
                $supplierRegion = \array_udiff($supplierRegion, $exclude_region, function ($a, $b) {
                    return $a - $b;
                });
            }
            return (empty($supplierRegion) ? [0] : $supplierRegion);
        } else {
            return [0];
        }
    }
}
