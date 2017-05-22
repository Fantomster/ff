<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "franchisee".
 *
 * @property integer $id
 * @property integer $franchisee_id
 * @property string $country
 * @property string $city
 * @property string $belongs_to
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
            [['franchisee_id','belongs_to'], 'integer'],
            [['franchisee_id','country','city'], 'required'],
            [['country','city'], 'string'],
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
            'city' => 'Город',
            'belongs_to' => 'Исключение',
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
