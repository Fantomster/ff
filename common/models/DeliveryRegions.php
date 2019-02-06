<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "delivery_regions".
 *
 * @property int          $id                          Идентификатор записи в таблице
 * @property int          $supplier_id                 Идентификатор организации-поставщика, осуществляющей доставку
 *           товаров
 * @property string       $country                     Государство, на территорию которого осуществляется доставка
 * @property string       $locality                    Населённый пункт, в который осуществляется доставка
 * @property string       $administrative_area_level_1 Административный регион (1-й уровень), на территорию которого
 *           осуществляется доставка
 * @property int          $exception                   Показатель состояния одобрения показа товаров поставщика в
 *           Market (0 - не одобрено, 1 - одобрено)
 * @property string       $created_at                  Дата и время создания записи в таблице
 * @property string       $updated_at                  Дата и время последнего изменения записи в таблице
 *
 * @property Organization $supplier
 */
class DeliveryRegions extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%delivery_regions}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['supplier_id', 'country'], 'required'],
            [['supplier_id', 'exception'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['locality', 'administrative_area_level_1'], 'string', 'max' => 255],
            [['country'], 'required', 'message' => 'cfg'],
            [['supplier_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::className(), 'targetAttribute' => ['supplier_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'                          => 'ID',
            'supplier_id'                 => Yii::t('app', 'Supplier ID'),
            'country'                     => Yii::t('app', 'common.models.country', ['ru' => 'Страна']),
            'locality'                    => Yii::t('app', 'common.models.city', ['ru' => 'Город']),
            'exception'                   => Yii::t('app', 'common.models.exception', ['ru' => 'Исключение']),
            'administrative_area_level_1' => Yii::t('app', 'common.models.region', ['ru' => 'Область']),
            'created_at'                  => Yii::t('app', 'common.models.created_two', ['ru' => 'Создано']),
            'updated_at'                  => Yii::t('app', 'common.models.refreshed_two', ['ru' => 'Обновлено']),
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
     *
     * @param null $city
     * @param null $region
     * @return array
     * @throws \yii\db\Exception
     */
    public static function getSuppRegion($city = null, $region = null)
    {
        if (empty($city)) {
            $city = Yii::$app->request->cookies->get('locality');
        }

        if (empty($region)) {
            $region = Yii::$app->request->cookies->get('region');
        }

        $tblDR = self::tableName();
        
        $supplierRegions = (new \yii\db\Query())
                ->select(['d1.supplier_id'])
                ->from("$tblDR as d1")
                ->leftJoin(["d2" => "$tblDR"], "d1.supplier_id = d2.supplier_id and d1.exception != d2.exception")
                ->where(['d1.locality' => $city])
                ->orWhere(['and', 
                    ['d1.administrative_area_level_1' => $region], 
                    ['<', new \yii\db\Expression('length(d1.locality)'), 1]
                    ])
                ->andWhere(['d1.exception' => 0])
                ->andWhere('d2.exception is null')
                ->column();
        
        if (empty($supplierRegions)) {
            return [0];
        }
        
        return $supplierRegions;
    }
}
