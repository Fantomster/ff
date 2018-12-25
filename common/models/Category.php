<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "category".
 *
 * @property int    $id   Идентификатор записи в таблице
 * @property string $name Наименование категории товаров
 */
class Category extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%category}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'   => 'ID',
            'name' => Yii::t('app', 'common.models.category_two', ['ru' => 'Категория']),
        ];
    }

    /**
     * @return array
     */
    public static function allCategory()
    {
        return ArrayHelper::map(Category::find()->all(), 'id', 'name');
    }

    /**
     * @param $id
     * @return array|Category|\yii\db\ActiveRecord|null
     */
    public static function get_value($id)
    {
        $model = Category::find()->where(["id" => $id])->one();
        if (!empty($model)) {
            return $model;
        }
        return null;
    }
}
