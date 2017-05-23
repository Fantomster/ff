<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "franchisee".
 *
 * @property integer $id
 * @property integer $franchisee_id
 * @property string $exception
 * @property string $country
 * @property string $locality
 * @property string $administrative_area_level_1
 * @property string $created_at
 * @property string $updated_at
 * 
 * @property Franchisee[] $franchisee
 */
class FranchiseeGeo extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'franchisee_geo';
    }

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'value' => function ($event) {
                    return gmdate("Y-m-d H:i:s");
                },
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['franchisee_id','exception'], 'integer'],
            [['franchisee_id','country'], 'required'],
            [['country','locality','administrative_area_level_1'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'franchisee_id' => 'ID франши',
            'country' => 'Страна',
            'locality' => 'Город',
            'exception' => 'Исключение',
            'administrative_area_level_1' => 'Область',
            'created_at' => 'Создано',
            'updated_at' => 'Обновлено',
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFranchisee() {
        return $this->hasMany(Franchisee::className(), ['franchisee_id' => 'id']);
    }
}
