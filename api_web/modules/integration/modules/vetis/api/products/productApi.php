<?php

namespace api_web\modules\integration\modules\vetis\api\products;

use common\models\vetis\VetisProductByType;
use common\models\vetis\VetisProductItem;
use common\models\vetis\VetisSubproductByProduct;
use api_web\modules\integration\modules\vetis\api\baseApi;

/**
 * Class productApi
 *
 * @package api_web\modules\integration\modules\vetis\api\products
 */
class productApi extends baseApi
{
    /**
     *
     */
    public function init()
    {
        $this->system = 'product';
        $this->wsdlClassName = Products::class;
        parent::init();
    }

    /**
     * Получение записи продукта по GUID
     *
     * @param $GUID
     * @return mixed|null
     */
    public function getProductByGuid($GUID)
    {
        VetisProductByType::getUpdateData($this->org_id);
        $product = VetisProductByType::findOne(['guid' => $GUID]);

        if (!empty($product)) {
            return unserialize($product->data);
        }

        return null;
    }

    /**
     * Получение записи вида продукции по GUID
     *
     * @param $GUID
     * @return mixed|null
     */
    public function getSubProductByGuid($GUID)
    {
        VetisSubproductByProduct::getUpdateData($this->org_id);
        $subProduct = VetisSubproductByProduct::findOne(['guid' => $GUID]);

        if (!empty($subProduct)) {
            return unserialize($subProduct->data);
        }

        return null;
    }

    /**
     * Получение списка продуктов по типу
     *
     * @param $type
     * @return mixed
     */
    public function getProductByTypeList($type)
    {
        VetisProductByType::getUpdateData(\Yii::$app->user->identity->organization_id);
        $result = VetisProductByType::find()->where(['productType' => $type])->all();

        if (!empty($result)) {
            $list = [];
            foreach ($result as $item) {
                $list[] = unserialize($item->data);
            }
            return $list;
        }
        return [];
    }

    /**
     * Получение списка видов продукции по продукту
     *
     * @param $guid
     * @return mixed
     */
    public function getSubProductByProductList($guid)
    {
        VetisProductItem::getUpdateData(\Yii::$app->user->identity->organization_id);
        $result = VetisProductItem::find()->where(['productGuid' => $guid])->all();

        if (!empty($result)) {
            $list = [];
            foreach ($result as $item) {
                $list[] = unserialize($item->data);
            }
            return $list;
        }
        return [];
    }

    /**
     * Получение списка продуктов по виду и продукту
     *
     * @param $productType
     * @param $product_guid
     * @param $subproduct_guid
     * @return mixed
     */
    public function getProductItemList($productType, $product_guid, $subproduct_guid)
    {
        VetisProductItem::getUpdateData(\Yii::$app->user->identity->organization_id);
        $result = VetisProductItem::find()->where(['productType' => $productType, 'product_guid' => $product_guid, 'subproduct_guid' => $subproduct_guid])->all();

        if (!empty($result)) {
            $list = [];
            foreach ($result as $item) {
                $list[] = unserialize($item->data);
            }
            return $list;
        }
        return [];
    }

    /**
     * Составление запроса на списка предприятий мира
     *
     * @param $options
     * @return getProductChangesListRequest
     * @throws \Exception
     */
    public function getProductChangesList($options)
    {
        require_once(__DIR__ . "/Products.php");
        $request = new getProductChangesListRequest();
        if (array_key_exists('listOptions', $options)) {
            $request->listOptions = $options['listOptions'];
        }

        if (!array_key_exists('listOptions', $options)) {
            throw new \Exception(\Yii::t('api_web', 'startDate field is not specified', ['ru'=>'Начальная дата неуказана']));
        }

        $request->updateDateInterval = new DateInterval();
        $request->updateDateInterval->beginDate = date('Y-m-d', strtotime($options['startDate'])) . 'T' . date('H:i:s', strtotime($options['startDate']));
        $request->updateDateInterval->endDate = date('Y-m-d') . 'T' . date('H:i:s') . '+03:00';

        return $request;
    }

    /**
     * Составление запроса на списка предприятий России
     *
     * @param $options
     * @return getSubProductChangesListRequest
     * @throws \Exception
     */
    public function getSubProductChangesList($options)
    {
        require_once(__DIR__ . "/Products.php");
        $request = new getSubProductChangesListRequest();
        if (array_key_exists('listOptions', $options)) {
            $request->listOptions = $options['listOptions'];
        }

        if (!array_key_exists('listOptions', $options)) {
            throw new \Exception(\Yii::t('api_web', 'startDate field is not specified', ['ru'=>'Начальная дата неуказана']));
        }

        $request->updateDateInterval = new DateInterval();
        $request->updateDateInterval->beginDate = date('Y-m-d', strtotime($options['startDate'])) . 'T' . date('H:i:s', strtotime($options['startDate']));
        $request->updateDateInterval->endDate = date('Y-m-d') . 'T' . date('H:i:s') . '+03:00';

        return $request;
    }

    /**
     * Составление запроса на списка ХС России
     *
     * @param $options
     * @return getProductItemChangesListRequest
     * @throws \Exception
     */
    public function getProductItemChangesList($options)
    {
        require_once(__DIR__ . "/Products.php");
        $request = new getProductItemChangesListRequest();
        if (array_key_exists('listOptions', $options)) {
            $request->listOptions = $options['listOptions'];
        }

        if (!array_key_exists('listOptions', $options)) {
            throw new \Exception(\Yii::t('api_web', 'startDate field is not specified', ['ru'=>'Начальная дата неуказана']));
        }

        $request->updateDateInterval = new DateInterval();
        $request->updateDateInterval->beginDate = date('Y-m-d', strtotime($options['startDate'])) . 'T' . date('H:i:s', strtotime($options['startDate']));
        $request->updateDateInterval->endDate = date('Y-m-d') . 'T' . date('H:i:s') . '+03:00';

        return $request;
    }
}