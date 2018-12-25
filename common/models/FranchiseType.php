<?php

namespace common\models;

use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "franchise_type".
 *
 * @property int    $id    Идентификатор записи в таблице
 * @property string $name  Наименование типа франчайзи
 * @property string $share Процент дохода от франшизы
 * @property string $price Цена за покупку франшизы
 */
class FranchiseType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%franchise_type}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'share', 'price'], 'required'],
            [['share', 'price'], 'number'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'    => 'ID',
            'name'  => 'Name',
            'share' => 'Share',
            'price' => 'Price',
        ];
    }

    /**
     * array of all organization types
     *
     * @return array
     */
    public static function getList()
    {
        $models = FranchiseType::find()
            ->select(['id', 'name'])
            ->asArray()
            ->all();

        return ArrayHelper::map($models, 'id', 'name');
    }
}
