<?php

namespace common\models;

use api_web\exceptions\ValidationException;
use Yii;
use yii\web\BadRequestHttpException;

/**
 * This is the model class for table "catalog".
 *
 * @property integer $id
 * @property integer $supp_org_id
 * @property string $name
 * @property integer $status
 * @property integer $type
 * @property string $created_at
 * @property string $updated_at
 * @property integer $currency_id
 * @property string $index_column
 * @property string $main_index
 * @property string $mapping
 *
 * @property Vendor $vendor
 * @property Currency $currency
 * @property integer $positionsCount
 */
class Catalog extends \yii\db\ActiveRecord
{

    const BASE_CATALOG = 1;
    const CATALOG = 2;
    const NON_CATALOG = 0;
    const CATALOG_BASE_NAME = 'Главный каталог';
    const STATUS_ON = 1;
    const STATUS_OFF = 0;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'catalog';
    }

    //auto created_at && updated_at 
    public function behaviors()
    {
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
    public function rules()
    {
        return [
            [['name', 'supp_org_id', 'type'], 'required'],
            [['supp_org_id', 'type', 'status'], 'integer'],
            [['created_at', 'mapping', 'index_column'], 'safe'],
            [['name'], 'string', 'max' => 255],
                //['type', 'uniqueBaseCatalog'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'supp_org_id' => 'Org Supp ID',
            'type' => 'Type',
            'status' => 'Status',
            'created_at' => 'Create Datetime',
        ];
    }

    public function uniqueBaseCatalog()
    {
        if ($this->type == 1) {
            $baseCheck = self::find()->where(['supp_org_id' => $this->supp_org_id, 'type' => 1])->all();
            if ($baseCheck) {
                $this->addError('type', Yii::t('app', 'common.models.one_catalog', ['ru' => 'Может быть только один базовый каталог']));
            }
        }
    }

    public static function getNameCatalog($id)
    {
        $catalogName = Catalog::find()
                        ->where(['id' => $id])->one();
        return $catalogName;
    }

    public static function get_value($id)
    {
        $model = Catalog::find()->where(["id" => $id])->one();
        if (!empty($model)) {
            return $model;
        }
        return null;
    }

    public static function GetCatalogs($type, $vendorId = null)
    {
        $catalog = Catalog::find()
                        ->select(['id', 'status', 'name', 'created_at', 'currency_id'])
                        ->where(['supp_org_id' => $vendorId ? $vendorId : \common\models\User::getOrganizationUser(Yii::$app->user->id), 'type' => $type])->all();
        return $catalog;
    }

    public function getVendor()
    {
        return $this->hasOne(Organization::className(), ['id' => 'supp_org_id']);
    }

    public function getCurrency()
    {
        return $this->hasOne(Currency::className(), ['id' => 'currency_id']);
    }

    public function addCatalog($arrCatalog, bool $isWebApi = false)
    {
        if ($arrCatalog === Array() && !$isWebApi) {
            throw new BadRequestHttpException(Yii::t('message', 'frontend.controllers.client.empty_catalog', ['ru' => 'Каталог пустой!']));
        }

        $numberPattern = '/^\s*[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?\s*$/';
        if (count($arrCatalog) > CatalogBaseGoods::MAX_INSERT_FROM_XLS) {
            throw new BadRequestHttpException(Yii::t('message', 'frontend.controllers.client.more_position', ['ru' => 'Чтобы добавить больше <strong> {max} </strong> позиций, пожалуйста свяжитесь с нами', 'max' => CatalogBaseGoods::MAX_INSERT_FROM_XLS])
            . '<a href="mailto://info@mixcart.ru" target="_blank" class="text-success">info@mixcart.ru</a>');
        }
        $productNames = [];
        foreach ($arrCatalog as $arrCatalogs) {
            if (!isset($arrCatalogs['product'])) {
                throw new BadRequestHttpException(Yii::t('message', 'frontend.controllers.client.empty_catalog', ['ru' => 'Каталог пустой!']));
            }
            $product = strip_tags(trim($arrCatalogs['product']));
            $price = floatval(trim(str_replace(',', '.', $arrCatalogs['price'])));
            $ed = strip_tags(trim($arrCatalogs['ed']));
            if (empty($product)) {
                $result = ['attribute' => 'product', 'message' => Yii::t('error', 'frontend.controllers.client.empty_field', ['ru' => 'Ошибка: Пустое поле'])];
                throw new ValidationException($result);
            }

            $price = str_replace(',', '.', $price);
            if (empty($price) || !preg_match($numberPattern, $price)) {
                $result = ['attribute' => 'price', 'message' => Yii::t('message', 'frontend.controllers.client.wrong_price', ['ru' => 'Ошибка: <strong>[Цена]</strong> в неверном формате!'])];
                throw new ValidationException($result);
            }
            if (empty($units) || $units < 0) {
                $units = 0;
            }
            $units = str_replace(',', '.', $units);
            if (!empty($units) && !preg_match($numberPattern, $units)) {
                $result = ['attribute' => 'units', 'message' => Yii::t('message', 'frontend.controllers.client.wrong_measure', ['ru' => 'Ошибка: <strong>[Кратность]</strong> товара в неверном формате'])];
                throw new ValidationException($result);
            }
            if (empty($ed)) {
                $result = ['attribute' => 'ed', 'message' => Yii::t('message', 'frontend.controllers.client.empty', ['ru' => 'Ошибка: Пустое поле <strong>[Единица измерения]</strong>!'])];
                throw new ValidationException($result);
            }
            array_push($productNames, mb_strtolower(trim($product)));
        }

        if (count($productNames) !== count(array_flip($productNames))) {
            throw new BadRequestHttpException(Yii::t('app', 'Вы пытаетесь загрузить одну или более позиций с одинаковым наименованием!'));
        }
    }

    /**
     * @param               $check
     * @param               $get_supp_org_id
     * @param               $currentUser
     * @param               $arrCatalog
     * @param Currency|null $currency
     * @return int
     * @throws ValidationException
     */
    public function addBaseCatalog($check, $get_supp_org_id, $currentUser, $arrCatalog, Currency $currency = null)
    {
        /**
         *
         * 2) Создаем базовый и каталог для ресторана
         *
         * */
        if ($check['eventType'] == 5) {
            $newBaseCatalog = new Catalog();
            $newBaseCatalog->supp_org_id = $get_supp_org_id;
            $newBaseCatalog->name = Yii::t('app', 'Главный каталог');
            $newBaseCatalog->type = Catalog::BASE_CATALOG;
            $newBaseCatalog->status = Catalog::STATUS_ON;
            if (!is_null($currency)) {
                $newBaseCatalog->currency_id = $currency->id;
            }
            if (!$newBaseCatalog->save()){
                throw new ValidationException($newBaseCatalog->getFirstErrors());
            }
            $newBaseCatalog->refresh();
            $lastInsert_base_cat_id = $newBaseCatalog->id;
        } else {
            //Поставщик зарегистрирован, но не авторизован
            //проверяем, есть ли у поставщика Главный каталог и если нету, тогда создаем ему каталог
            if (Catalog::find()->where(['supp_org_id' => $get_supp_org_id, 'type' => Catalog::BASE_CATALOG])->exists()) {
                $lastInsert_base_cat_id = Catalog::find()->select('id')->where(['supp_org_id' => $get_supp_org_id, 'type' => Catalog::BASE_CATALOG])->one();
                $lastInsert_base_cat_id = $lastInsert_base_cat_id['id'];
            } else {
                $newBaseCatalog = new Catalog();
                $newBaseCatalog->supp_org_id = $get_supp_org_id;
                $newBaseCatalog->name = Yii::t('message', 'frontend.controllers.client.main_cat', ['ru' => 'Главный каталог']);
                $newBaseCatalog->type = Catalog::BASE_CATALOG;
                $newBaseCatalog->status = Catalog::STATUS_ON;
                if (isset($currency)) {
                    $newBaseCatalog->currency_id = $currency->id;
                }
                if (!$newBaseCatalog->save()){
                    throw new ValidationException($newBaseCatalog->getFirstErrors());
                }
                $newBaseCatalog->refresh();
                $lastInsert_base_cat_id = $newBaseCatalog->id;
            }
        }

        $newCatalog = new Catalog();
        $newCatalog->supp_org_id = $get_supp_org_id;
        $newCatalog->name = ($currentUser->organization->name == "") ? $currentUser->email : $currentUser->organization->name;
        $newCatalog->type = Catalog::CATALOG;
        $newCatalog->status = Catalog::STATUS_ON;
        if (isset($currency)) {
            $newCatalog->currency_id = $currency->id;
        }
        if (!$newCatalog->save()){
            throw new ValidationException($newBaseCatalog->getFirstErrors());
        }
        $lastInsert_cat_id = $newCatalog->id;
        $newCatalog->refresh();

        /**
         *
         * 3 и 4) Создаем каталог базовый и его продукты, создаем новый каталог для ресторана и забиваем продукты на основе базового каталога
         *
         * */
        $article_create = 0;
        foreach ($arrCatalog as $arrCatalogs) {
            $article_create++;
            $article = $article_create;
            $product = strip_tags(trim($arrCatalogs['product']));
            $units = null;

            if (empty($units) || $units < 0) {
                $units = null;
            }
            $price = strip_tags(trim($arrCatalogs['price']));
            $ed = strip_tags(trim($arrCatalogs['ed']));
            $price = str_replace(',', '.', $price);
            if (substr($price, -3, 1) == '.') {
                $price = explode('.', $price);
                $last = array_pop($price);
                $price = join($price, '') . '.' . $last;
            } else {
                $price = str_replace('.', '', $price);
            }
            $newProduct = new CatalogBaseGoods();
            $newProduct->scenario = "import";
            $newProduct->cat_id = $lastInsert_base_cat_id;
            $newProduct->supp_org_id = $get_supp_org_id;
            $newProduct->article = (string) $article;
            $newProduct->product = $product;
            $newProduct->units = $units;
            $newProduct->price = $price;
            $newProduct->ed = $ed;
            $newProduct->status = CatalogBaseGoods::STATUS_ON;
            $newProduct->market_place = CatalogBaseGoods::MARKETPLACE_OFF;
            $newProduct->deleted = CatalogBaseGoods::DELETED_OFF;
            if (!$newProduct->save()){
                throw new ValidationException($newBaseCatalog->getFirstErrors());
            }
            $newProduct->refresh();

            $lastInsert_base_goods_id = $newProduct->id;

            $newGoods = new CatalogGoods();
            $newGoods->cat_id = $lastInsert_cat_id;
            $newGoods->base_goods_id = $lastInsert_base_goods_id;
            $newGoods->price = $price;
            if (!$newGoods->save()){
                throw new ValidationException($newBaseCatalog->getFirstErrors());
            }
            $newGoods->refresh();
        }
        return $lastInsert_cat_id;
    }

    public function makeSnapshot()
    {
        if ($this->type !== self::BASE_CATALOG) {
            return false;
        }
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $newSnapshot = new CatalogSnapshot();
            $newSnapshot->cat_id = $this->id;
            $newSnapshot->main_index = $this->main_index;
            $newSnapshot->currency_id = $this->currency_id;
            $newSnapshot->save();
            $sql = "INSERT INTO catalog_snapshot_content "
                    . "(snapshot_id,article, product, status, market_place, deleted, price, units, category_id, note, ed, image, brand, region, weight, mp_show_price, barcode, edi_supplier_article, ssid) "
                    . "(SELECT :snapshot_id"
                    . ",article, product, status, market_place, deleted, price, units, category_id, note, ed, image, brand, region, weight, mp_show_price, barcode, edi_supplier_article, ssid "
                    . "FROM catalog_base_goods WHERE cat_id = :cat_id AND deleted = 0)";
            \Yii::$app->db->createCommand($sql)
                    ->bindValues([":snapshot_id" => $newSnapshot->id, ":cat_id" => $this->id])
                    ->execute();
            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            return false;
        }
    }

    public function restoreLastSnapshot($saveCurrent = false)
    {
        $lastSnapshot = CatalogSnapshot::find()->orderBy(['id' => SORT_DESC])->limit(1)->one();
        if (empty($lastSnapshot)) {
            return false;
        }
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $this->deleteAllProducts($saveCurrent);
            $this->main_index = $lastSnapshot->main_index;
            $this->currency_id = $this->currency_id;
            if ($this->save()) {
                $sql = "INSERT INTO catalog_base_goods "
                        . "(supp_org_id, cat_id,article, product, status, market_place, deleted, price, units, category_id, note, ed, image, brand, region, weight, mp_show_price, barcode, edi_supplier_article, ssid) "
                        . "(SELECT :supp_org_id, :cat_id"
                        . ",article, product, status, market_place, deleted, price, units, category_id, note, ed, image, brand, region, weight, mp_show_price, barcode, edi_supplier_article, ssid "
                        . "FROM catalog_snapshot_content WHERE snapshot_id = :snapshot_id)";
                \Yii::$app->db->createCommand($sql)
                        ->bindValues([":snapshot_id" => $lastSnapshot->id, ":cat_id" => $this->id, ":supp_org_id" => $this->supp_org_id])
                        ->execute();
                $transaction->commit();
                return true;
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            return false;
        }
    }

    public function deleteAllProducts($save = true)
    {
        if ($this->positionsCount == 0) {
            return true;
        }
        if ($save && $this->makeSnapshot()) {
            CatalogBaseGoods::updateAll(["deleted" => 1], ["cat_id" => $this->id]);
            return true;
        }
        return false;
    }

    public static function getMainIndexesList()
    {
        return [
            'product' => Yii::t('message', 'frontend.views.vendor.name_of_good', ['ru' => 'Наименование']),
            'article' => Yii::t('message', 'frontend.views.vendor.art_five', ['ru' => 'Артикул']),
            'ssid' => Yii::t('message', 'frontend.views.vendor.ssid', ['ru' => 'SSID']),
        ];
    }

    public static function isMainIndexValid($index)
    {
        $indexes = self::getMainIndexesList();
        return (in_array($index, array_keys($indexes)));
    }

    public function getPositionsCount()
    {
        if ($this->type == self::BASE_CATALOG) {
            return CatalogBaseGoods::find()->where(['cat_id' => $this->id, 'deleted' => 0])->count();
        } else {
            return CatalogGoods::find()->joinWith('baseProduct')->where([CatalogGoods::tableName() . '.cat_id' => $this->cat_id, 'deleted' => 0])->count();
        }
    }

}
