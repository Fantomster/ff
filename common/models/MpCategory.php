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
 * @property string $title
 * @property string $text
 * @property string $description
 * @property string $keywords
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
            [['name', 'title'], 'string', 'max' => 255],
            [['name', 'title', 'text', 'description', 'keywords'], 'filter', 'filter' => '\yii\helpers\HtmlPurifier::process'],
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
                'forceUpdate' => true,
                'ensureUnique' => true,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => Yii::t('app', 'common.models.category_mp', ['ru'=>'Категория']) . ' (MP)',
            'parent' => 'Parent',
        ];
    }

    public static function getCountProduct($id) {
        return CatalogBaseGoods::find()->where(["category_id" => $id])->count();
    }

    public static function getCategory($id) {
        $cat = Yii::t('app', MpCategory::find()->where(["id" => $id])->one()->name);
        return $cat;
    }

    public static function allCategory() {
        $mp_ed = ArrayHelper::map(MpCategory::find()->all(), 'id', 'name');
        foreach ($mp_ed as &$item){
            $item = Yii::t('app', $item);
        }
        return $mp_ed;
    }

}
