<?php

namespace common\models;

use Yii;
use yii\data\ActiveDataProvider;
use common\behaviors\ImageUploadBehavior;
use Imagine\Image\ManipulatorInterface;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
/**
 * This is the model class for table "catalog_base_goods".
 *
 * @property integer $id
 * @property integer $cat_id
 * @property integer $category_id
 * @property string $article
 * @property string $product
 * @property number $units
 * @property integer $price
 * @property integer $status
 * @property integer $market_place
 * @property integer $deleted
 * @property string $created_at
 * @property string $updated_at
 * @property string $image
 * @property string $imageUrl
 * @property string $miniImageUrl
 * @property string $brand
 * @property string $region
 * @property string $weight
 * @property file $importCatalog
 * @property string $note
 * @property string $ed
 * @property integer $mp_show_price
 * 
 * @property MpCountry $mpRegion
 * @property Organization $vendor
 * @property MpCategory $category
 * @property MpCategory $mainCategory
 */
class CatalogBaseGoods extends \yii\db\ActiveRecord {

    const STATUS_ON = 1;
    const STATUS_OFF = 0;
    const MARKETPLACE_ON = 1;
    const MARKETPLACE_OFF = 0;
    const DELETED_ON = 1;
    const DELETED_OFF = 0;
    const DEFAULT_IMAGE = 'data:image/png;base64,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';
    
    const ES_UPDATE = 1; //обновление существующего или добавление нового товара, крон на каждые 2 мин
    const ES_DELETED = 2; //Удаление, крон на каждые 2 мин
    const ES_MASS_UPDATED = 3; //в случае,  если обновили весь каталог через файл, крон работает ночью, порционально добавляет в бд по 1000 товаров
    const ES_MASS_DELETED = 4; //массовое удаление, пока не используется, но может понадобиться
    
    
    public $USER_TYPE;
    public $searchString; 
    
    public $resourceCategory = 'image';
    
    public $sub1;
    public $sub2;

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return ArrayHelper::merge(parent::behaviors(), [
                    [
                        'class' => ImageUploadBehavior::className(),
                        'attribute' => 'image',
                        'scenarios' => ['default', 'marketPlace'],
                        'path' => '@app/web/upload/temp/',
                        'url' => '/upload/temp/',
                        'thumbs' => [
                            'image' => ['width' => 432, 'height' => 243, 'mode' => ManipulatorInterface::THUMBNAIL_OUTBOUND],
                            'mini' => ['width' => 96, 'height' => 54, 'mode' => ManipulatorInterface::THUMBNAIL_OUTBOUND],
                        ],
                    ],
        ]);
    }
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'catalog_base_goods';
    }
    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['cat_id','article','price','product','ed'], 'required'],
            [['cat_id', 'category_id','supp_org_id', 'status', 'market_place', 'deleted', 'mp_show_price'], 'integer'],
            [['market_place', 'mp_show_price'], 'default', 'value' => 0],
            [['article'], 'string', 'max' => 50],
            [['product','brand','region','weight'], 'string', 'max' => 255],
            [['note'], 'string', 'max' => 255],
            [['ed'], 'string', 'max' => 255],
            [['image'], 'image', 'extensions' => 'jpg, jpeg, png', 'maxSize' => 2097152, 'tooBig' => 'Размер файла не должен превышать 2 Мб'],//, 'maxSize' => 4194304, 'tooBig' => 'Размер файла не должен превышать 4 Мб'
            [['units'], 'number', 'numberPattern' => '/^\s*[-+]?[0-9]*[.,]?(NULL)?[0-9]+([eE][-+]?[0-9]+)?\s*$/'],
            [['price'], 'number', 'numberPattern' => '/^\s*[-+]?[0-9]*[.,]?[0-9]+([eE][-+]?[0-9]+)?\s*$/'],
            [['price'], 'number', 'min'=>0.1],
            [['sub1', 'sub2'], 'required', 
                'when' => function($model) { 
                    return $model->market_place == self::MARKETPLACE_ON;
                },
                'whenClient' => 'function(attribute, value) {
                    return ($("#catalogbasegoods-market_place").val() == ' . self::MARKETPLACE_ON .');
                }',
                'message' => 'Укажите категорию товара',
                'on' => 'marketPlace',
            ],
            [['category_id'], 'required',
                'when' => function($model) {
                    return $model->market_place == self::MARKETPLACE_ON;
                },
                'except' => 'marketPlace',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'cat_id' => 'Каталог',
            'category_id' => 'Категория',
            'article' => 'Артикул',
            'product' => 'Товар',
            'units' => 'Кратность',
            'price' => 'Цена (руб.)',
            'status' => 'Статус',
            'market_place' => 'Market_place',
            'deleted' => 'Deleted',
            'note' => 'Комментарий',
            'ed' => 'Единица измерения',
            'image' => 'Картинка продукта',
            'brand' => 'Производитель',
            'region' => 'Страна производитель',
            'weight' => 'Вес',
            'mp_show_price' => 'Показывать цену в F-MARKET',
                //'importCatalog'=>'Files'
        ];
    }

    public function beforeSave($insert)
    {
    if (parent::beforeSave($insert)) {
            $this->price = str_replace(",", ".", $this->price);
            $this->units = str_replace(",", ".", $this->units);
            return true;
        }
        return false;
    }
    public function search($params, $id) {
        $query = CatalogBaseGoods::find()->select(['id', 'cat_id', 'category_id', 'article', 'product', 'units', 'price','note','ed', 'status', 'market_place'])->where(['cat_id' => $id, 'deleted' => '0']);
        //$query->andFilterWhere(['like', 'product', '']);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $dataProvider->setSort([
            'attributes' => [
                'id',
                'cat_id',
                'category_id',
                'market_place',
                'article',
                'product',
                'units',
                'price',
                'status',
                'note',
                'ed',
            ]
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }
        
        $query->orFilterWhere(['like', 'article', $this->searchString])
              ->orFilterWhere(['like', 'product', $this->searchString]);

        return $dataProvider;
    }

    public static function GetCatalog() {
        $catalog = CatalogBaseGoods::find()
                        ->where(['supp_org_id' => \common\models\User::getOrganizationUser(Yii::$app->user->id), 'type' => \common\models\Catalog::BASE_CATALOG])->all();
        return $catalog;
    }

    public static function get_value($id) {
        $model = CatalogBaseGoods::find()->where(["id" => $id])->one();
        if (!empty($model)) {
            return $model;
        }
        return null;
    }

    public static function get_no_active_product($id) {
        $model = CatalogBaseGoods::find()->select('id')->where(["id" => $id, 'status' => CatalogBaseGoods::STATUS_OFF])->all();
        return $model;
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVendor() {
        return $this->hasOne(Organization::className(), ['id' => 'supp_org_id']);
    }
    public function getCategory() {
        return $this->hasOne(MpCategory::className(), ['id' => 'category_id']);
    }
    /**
     * @return string url to product image
     */
    /*public function getImageUrl()
    {
        return $this->image ? $this->getThumbUploadUrl('image', 'image') : self::DEFAULT_IMAGE;
    }*/
    public function getImageUrl()
    {
        if($this->image){
           return $this->getThumbUploadUrl('image', 'image'); 
        }else{
            if($this->category_id){
                return Url::to('@web/fmarket/images/image-category/'.$this->mainCategory->id.".jpg", true);
            }else{
                return self::DEFAULT_IMAGE;
            }
        }
            
    }
    public function getMpRegion() {
        return $this->hasOne(mpCountry::className(), ['id' => 'region']);
    }
    
    public function getMiniImageUrl() {
        return $this->image ? $this->getThumbUploadUrl('image', 'mini') : self::DEFAULT_IMAGE;
    }
    
    public function getSubCategory() {
        return $this->hasOne(MpCategory::className(), ['id' => 'category_id']);
    }
    
    public function getMainCategory() {
        return MpCategory::find()->where(['id' => $this->category->parent]);
    }
    
    public static function getCurCategory($id) {
    $parent = MpCategory::find()->where(['id' => $id])->one()->parent;
    return MpCategory::find()->where(['id' => $parent])->one();
    }
    
}
