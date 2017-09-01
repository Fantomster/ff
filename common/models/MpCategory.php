<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;
use common\behaviors\SluggableBehavior;

/**
 * This is the model class for table "mp_category".
 *
 * @property integer $id
 * @property string $name
 * @property integer $parent
 */
class MpCategory extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'mp_category';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['name'], 'required'],
            [['parent'], 'integer'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    public function behaviors() {
        return [
            'slug' => [
                'class' => SluggableBehavior::className(),
                'attribute' => 'name',
                'slugAttribute' => 'slug',
                'transliterator' => 'Russian-Latin/BGN; NFKD',
                //Set this to true, if you want to update a slug when source attribute has been changed
                'forceUpdate' => true
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'parent' => 'Parent',
        ];
    }

    public static function getCountProduct($id) {
        return CatalogBaseGoods::find()->where(["category_id" => $id])->count();
    }

    public static function getCategory($id) {
        return MpCategory::find()->where(["id" => $id])->one()->name;
    }

    public static function allCategory() {
        return ArrayHelper::map(MpCategory::find()->all(), 'id', 'name');
    }

}
