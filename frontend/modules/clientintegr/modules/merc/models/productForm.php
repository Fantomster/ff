<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 20.09.2018
 * Time: 12:03
 */

namespace frontend\modules\clientintegr\modules\merc\models;


use common\models\vetis\VetisProductItem;
use Real\Validator\Gtin;

class productForm extends VetisProductItem
{
    /**
     * Список производителей
     *
     * @var array
     */
    public $producers;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'productType','product_guid','subproduct_guid'], 'required'],
            [['productType', 'correspondsToGost', 'packagingQuantity'], 'integer'],
            [['gost'],'checkGost'],
            [['globalID'], 'checkGlobalID'],
            [['name', 'code', 'product_uuid', 'product_guid', 'subproduct_uuid',
                'subproduct_guid', 'gost', 'producer_uuid', 'producer_guid', 'tmOwner_uuid', 'tmOwner_guid',
                'packagingType_guid', 'packagingType_uuid', 'unit_uuid', 'unit_guid'], 'string', 'max' => 255],
            [['packagingVolume'], 'number', 'numberPattern' => '/^\s*[-+]?[0-9]*[.,]?[0-9]+([eE][-+]?[0-9]+)?\s*$/'],
            ['packagingVolume', 'filter', 'filter' => function ($value) {
                $newValue = isset($value) ? (0 + str_replace(',', '.', $value)) : null;
                return $newValue;
            }],
            [['packagingVolume'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'name' => 'Наименование продукции',
            'code' => 'Артикул',
            'globalID' => 'GTIN',
            'productType' => 'Тип продукции',
            'product_uuid' => 'Продукция',
            'product_guid' => 'Продукция',
            'subproduct_uuid' => 'Вид продукции',
            'subproduct_guid' => 'Вид продукции',
            'correspondsToGost' => 'Соответствие ГОСТ',
            'gost' => 'ГОСТ',
            'producer_uuid' => 'Producer Uuid',
            'producer_guid' => 'Producer Guid',
            'tmOwner_uuid' => 'Tm Owner Uuid',
            'tmOwner_guid' => 'Tm Owner Guid',
            'packagingType_guid' => 'Упаковка',
            'packagingType_uuid' => 'Упаковка',
            'unit_uuid' => 'Единица Измерения',
            'unit_guid' => 'Единица Измерения',
            'packagingQuantity' => 'Количество единиц упаковки',
            'packagingVolume' => 'Объём единицы упаковки товара',
        ];
    }

    public function checkGlobalID()
    {
        if(isset($this->globalID)) {
            try {
                $gtin = Gtin\Factory::create($this->globalID);
            } catch (Gtin\NonNormalizable $e) {
                switch ($e->getCode()) {
                    case Gtin\NonNormalizable::CODE_LENGTH_14:
                        $message = 'Длина больше 14 символов';
                        break;
                    case Gtin\NonNormalizable::CODE_LENGTH_8:
                        $message = 'Длина меньше 8 символов';
                        break;
                    case Gtin\NonNormalizable::CODE_LENGTH_KEY:
                        $message = 'Длина не соответствует вариации GTIN (GTIN-8, GTIN-12, GTIN-13, GTIN-14)';
                        break;
                    case Gtin\NonNormalizable::CODE_DIGITS:
                        $message = 'Некоторые символы не являются цифрами';
                        break;
                    case Gtin\NonNormalizable::CODE_PREFIX:
                        $message = 'Префикс недействителен';
                        break;
                    default:
                        $message = 'Неправильный номер (не сходится контрольная сумма)';
                        break;
                }
                $this->addError('globalID', $message);
            }
        }
    }

    public function checkGost()
    {
        if($this->correspondsToGost)
        {
            if(empty($this->gost))
            {
                $this->addError('gost', 'ГОСТ должен быть заполнен');
            }
        }
    }
}
