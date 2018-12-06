<?php

namespace api_web\classes;

use common\models\RelationSuppRest;
use api_web\exceptions\ValidationException;
use common\helpers\ModelsCollection;
use common\models\CatalogGoods;
use common\models\CatalogTemp;
use common\models\CatalogTempContent;
use common\models\Organization;
use Yii;
use common\models\Catalog;
use common\models\CatalogBaseGoods;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\data\Pagination;
use yii\db\Query;
use yii\web\BadRequestHttpException;
use api_web\components\WebApi;

/**
 * Class CatalogWebApi
 *
 * @package api_web\classes
 */
class CatalogWebApi extends WebApi
{

    /**
     * Смена уникального индекса главного каталога
     *
     * @param Catalog $catalog
     * @param integer $index
     * @return array
     * @throws BadRequestHttpException
     */
    public function changeMainIndex($catalog, $index)
    {
        $isEmpty = !CatalogBaseGoods::find()->where(['cat_id' => $catalog->id, 'deleted' => false])->exists();
        if ($isEmpty) {
            $catalog->main_index = $index;
            $catalog->save();
            return [
                'result' => true
            ];
        } else {
            throw new BadRequestHttpException(\Yii::t('api_web', "catalog.not_empty", ['ru'=>'Каталог не пустой']));
        }
    }

    /**
     * Удаление главного каталога
     *
     * @param Catalog $catalog
     * @return array
     * @throws BadRequestHttpException
     */
    public function deleteMainCatalog($catalog)
    {
        $isEmpty = !CatalogBaseGoods::find()->where(['cat_id' => $catalog->id, 'deleted' => false])->exists();
        if ($isEmpty) {
            throw new BadRequestHttpException(\Yii::t('api_web', "catalog.is_empty", ['ru'=>'Каталог пустой']));
        } else {
            if ($catalog->deleteAllProducts()) {
                return [
                    'result' => true
                ];
            } else {
                throw new BadRequestHttpException(\Yii::t('api_web', "catalog.delete_failed", ['ru'=>'Невозможно удалить каталог']));
            }
        }
    }

    /**
     * Обновление инд. каталога
     *
     * @param $request
     * @return array
     * @throws \Throwable
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function uploadTemporary($request)
    {
        $this->validateRequest($request, ['vendor_id']);

        $catalog = $this->getPersonalCatalog($request['vendor_id'], $this->user->organization);
        if (empty($catalog)) {
            throw new BadRequestHttpException( \Yii::t('api_web', "base_catalog_not_found", ['ru'=>'Базовый каталог не найден']));
        }

        $vendorBaseCatalog = Catalog::findOne(['supp_org_id' => $request['vendor_id'], 'type' => Catalog::BASE_CATALOG]);
        if (empty($vendorBaseCatalog)) {
            $vendorBaseCatalog = new Catalog([
                'supp_org_id'  => $request['vendor_id'],
                'type'         => Catalog::BASE_CATALOG,
                'name'         => 'Главный каталог',
                'status'       => Catalog::STATUS_ON,
                'currency_id'  => $catalog->currency_id,
                'main_index'   => $catalog->main_index,
                'index_column' => $catalog->index_column
            ]);
            $vendorBaseCatalog->save();
        }

        $catalogTemp = CatalogTemp::findOne(['cat_id' => $catalog->id, 'user_id' => $this->user->id]);
        if (empty($catalogTemp)) {
            throw new BadRequestHttpException( \Yii::t('api_web', "catalog_temp_not_found", ['ru'=>'Временный каталог не найден']));
        }

        $catalogTempContent = (new Query())->select([
            'ctc.id',
            'ctc.article',
            'ctc.product',
            'coalesce(cbg.id, 0) cbg_id',
            'ctc.ed',
            'ctc.units',
            'ctc.price',
            'ctc.note',
            'cbg.status',
            'cbg.market_place',
            'cbg.created_at',
            'cbg.updated_at',
            'cbg.supp_org_id',
            'cbg.category_id',
            'cbg.image',
            'cbg.brand',
            'cbg.region',
            'cbg.weight',
            'cbg.es_status',
            'cbg.mp_show_price',
            'cbg.rating',
            'cbg.barcode',
            'cbg.edi_supplier_article',
            'cbg.ssid',
            'coalesce(cg.id, 0) cg_id',
            'cg.base_goods_id',
            'cg.vat cg_vat'
        ])
            ->from(CatalogTempContent::tableName() . ' ctc')
            ->leftJoin(CatalogBaseGoods::tableName() . ' cbg', "cbg.$catalog->main_index=ctc.$catalog->main_index"
                . " and cbg.cat_id=:vendorBaseCatId", [':vendorBaseCatId' => $vendorBaseCatalog->id])
            ->leftJoin(CatalogGoods::tableName() . ' cg', 'cg.base_goods_id=cbg.id and cg.cat_id=:cat_id',
                [':cat_id' => $catalog->id])
            ->where(['temp_id' => $catalogTemp->id])->all();

        if (empty($catalogTempContent)) {
            throw new BadRequestHttpException(\Yii::t('api_web', "catalog_temp_content_not_found", ['ru'=>'Содержимое временного каталога не найдено']));
        }

        if (!empty($this->getTempDuplicatePosition(['vendor_id' => $request['vendor_id']]))) {
            throw new BadRequestHttpException(\Yii::t('api_web', "catalog_temp_exists_duplicate", ['ru'=>'Временный каталог уже содержит данную позицию']));
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            CatalogBaseGoods::updateAll([
                'status'  => CatalogBaseGoods::STATUS_OFF,
                'deleted' => CatalogBaseGoods::DELETED_ON,
            ], [
                'supp_org_id' => $catalog->supp_org_id,
                'cat_id'      => $catalog->id
            ]);
            $arBatchInsert = [];
            /**
             * @var CatalogTempContent $tempRow
             */
            foreach ($this->gen($catalogTempContent) as $tempRow) {
                if ($tempRow['cbg_id'] == 0) {
                    $model = new CatalogBaseGoods([
                        'cat_id'      => $vendorBaseCatalog->id,
                        'article'     => $tempRow['article'],
                        'product'     => $tempRow['product'],
                        'supp_org_id' => $catalog->supp_org_id
                    ]);
                } else {
                    /**@var CatalogBaseGoods $model */
                    $model = \Yii::createObject([
                        'class'                => '\common\models\CatalogBaseGoods',
                        'id'                   => $tempRow['cbg_id'],
                        'cat_id'               => $vendorBaseCatalog->id,
                        'article'              => $tempRow['article'],
                        'product'              => $tempRow['product'],
                        'status'               => $tempRow['status'],
                        'deleted'              => CatalogBaseGoods::DELETED_OFF,
                        'created_at'           => $tempRow['created_at'],
                        'updated_at'           => $tempRow['updated_at'],
                        'supp_org_id'          => $tempRow['supp_org_id'],
                        'category_id'          => $tempRow['category_id'],
                        'image'                => $tempRow['image'],
                        'brand'                => $tempRow['brand'],
                        'region'               => $tempRow['region'],
                        'weight'               => $tempRow['weight'],
                        'es_status'            => $tempRow['es_status'],
                        'mp_show_price'        => $tempRow['mp_show_price'],
                        'rating'               => $tempRow['rating'],
                        'barcode'              => $tempRow['barcode'],
                        'edi_supplier_article' => $tempRow['edi_supplier_article'],
                        'ssid'                 => $tempRow['ssid'],
                    ]);
                    $model->setOldAttributes([
                        'id' => $tempRow['cbg_id'],
                    ]);
                }
                //Заполняем аттрибуты
                $model->ed = $tempRow['ed'];
                $model->units = $tempRow['units'];
                $model->price = $tempRow['price'];
                $model->note = $tempRow['note'];
                $model->status = CatalogBaseGoods::STATUS_ON;
                $model->deleted = CatalogBaseGoods::DELETED_OFF;
                //Если атрибуты изменились или новая запись, сохраняем модель
                if (!$model->save()) {
                    throw new ValidationException($model->getFirstErrors());
                }
                if ($tempRow['cg_id'] != 0) {
                    $catalogGood = \Yii::createObject([
                        'class'         => '\common\models\CatalogGoods',
                        'cat_id'        => $catalog->id,
                        'base_goods_id' => $tempRow['cbg_id'],
                        'vat'           => $tempRow['cg_vat'],
                    ]);
                    $catalogGood->setOldAttributes([
                        'id' => $tempRow['cg_id'],
                    ]);
                    $catalogGood->price = $model->price;
                    if (!$catalogGood->save()) {
                        throw new ValidationException($catalogGood->getFirstErrors());
                    }
                } else {
                    $catalogGood = new CatalogGoods();
                    $catalogGood->cat_id = $catalog->id;
                    $catalogGood->base_goods_id = $model->id;
                    $catalogGood->price = $model->price;
                    $arBatchInsert[] = $catalogGood;
                }
                if (count($arBatchInsert) > 499) {
                    (new ModelsCollection())->saveMultiple($arBatchInsert, 'db');
                    $arBatchInsert = [];
                }
            }
            (new ModelsCollection())->saveMultiple($arBatchInsert, 'db');
            //Убиваем временный каталог
            CatalogTempContent::deleteAll(['temp_id' => $catalogTemp->id]);
            $catalogTemp->delete();
            $transaction->commit();
            return ['result' => true];
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * @param $catalogTempContent
     * @return \Generator
     */
    function gen($catalogTempContent)
    {
        foreach ($catalogTempContent as $item) {
            yield $item;
        }
    }

    /**
     * Список ключей
     *
     * @return array
     */
    public function getKeys()
    {
        return [
            'product' => Yii::t('api_web', 'api_web.catalog.key.product', ['ru' => 'Нименование товара']),
            'article' => Yii::t('api_web', 'api_web.catalog.key.article', ['ru' => 'Артикул']),
            'other'   => Yii::t('api_web', 'api_web.catalog.key.other', ['ru' => 'Другое']),
        ];
    }

    /**
     * Удаление из временного каталога
     *
     * @param $request
     * @return array
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @throws \yii\web\BadRequestHttpException
     */
    public function deleteItemTemporary($request)
    {
        $this->validateRequest($request, ['temp_id', 'id']);

        $model = CatalogTempContent::findOne(['temp_id' => (int)$request['temp_id'], 'id' => (int)$request['id']]);
        if (empty($model)) {
            throw new BadRequestHttpException(\Yii::t('api_web', "catalog_temp_not_found", ['ru'=>'Временный каталог не найден']));
        }

        if (!$model->delete()) {
            throw new BadRequestHttpException(\Yii::t('api_web', "catalog.delete_failed", ['ru'=>'Невозможно удалить временный каталог']));
        }

        return ['result' => true];
    }

    /**
     * @param $request
     * @return array
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @throws \yii\web\BadRequestHttpException
     */
    public function deleteItemPersonalCatalog($request)
    {

        $this->validateRequest($request, ['vendor_id', 'product_id']);

        $catalog = $this->getPersonalCatalog($request['vendor_id'], $this->user->organization, true);
        if (empty($catalog)) {
            throw new BadRequestHttpException(\Yii::t('api_web', "catalog_not_found", ['ru'=>'Каталог не найден']));
        }

        $product = CatalogGoods::findOne([
            'base_goods_id' => $request['product_id'],
            'cat_id'        => $catalog->id]);
        if (!$product) {
            throw new BadRequestHttpException(\Yii::t('api_web', "product_not_found", ['ru'=>'Позиция не найдена']));
        }
        $success = $product->delete();

        return ['result' => (bool)$success];
    }

    /**
     * Поиск дублей в темповом каталоге
     *
     * @param array $request
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function getTempDuplicatePosition(array $request)
    {
        $this->validateRequest($request, ['vendor_id']);

        $catalog = $this->container->get('CatalogWebApi')->getPersonalCatalog($request['vendor_id'], $this->user->organization, true);
        $catalogTemp = CatalogTemp::findOne(['cat_id' => $catalog->id, 'user_id' => $this->user->id]);
        if (empty($catalogTemp)) {
            throw new BadRequestHttpException(\Yii::t('api_web', "catalog_temp_not_found", ['ru'=>'Временный каталог не найден']));
        }

        if (empty($catalogTemp->index_column)) {
            throw new BadRequestHttpException(\Yii::t('api_web', "catalog.main_index_empty", ['ru'=>'Пустой основной индекс']));
        }

        //Ключ, по которому ищем дубли
        $index = $catalogTemp->index_column;

        //Готовим джойн, по которому будем выцеплять дубли
        $innerJoin = (new Query())
            ->select([$index, 'COUNT(*) AS CountOf'])
            ->from(CatalogTempContent::tableName())
            ->where(['temp_id' => $catalogTemp->id])
            ->groupBy($index)
            ->having("CountOf > 1")
            ->createCommand()
            ->getRawSql();

        /**
         * Сам запрос на поиск дублей
         * можно вывести запрос, если вместо ->all() сделать
         * ->createCommand()->getRawSql();
         * и распечатать результат в $content
         */
        $content = (new Query())
            ->select('*')
            ->from(CatalogTempContent::tableName() . " as T1")
            ->where(["T1.temp_id" => $catalogTemp->id])
            ->innerJoin("({$innerJoin}) as T2", "T1.{$index} = T2.{$index}")
            ->orderBy("T1.{$index}")
            ->all();

        $result = [];

        //Если не нашли дублей
        if (empty($content)) {
            return $result;
        }

        foreach ($content as $row) {
            $result[$row[$index]][] = array_merge(['index_field' => $index], $this->prepareTempDuplicatePosition($row));
        }

        return $result;
    }

    /**
     * Автоматическое удаление дублей из загружаемого каталога
     *
     * @param array $request
     * @return array
     * @throws \Exception
     */
    public function autoDeleteDuplicateTemporary(array $request)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            //ID каталога темпового
            $temp_id = null;
            //Список id позиций которые будем удалять
            $ids = [];
            //Получаем список дублей
            $doubles = $this->getTempDuplicatePosition($request);
            foreach ($doubles as &$positions) {
                while (count($positions) > 1) {
                    $position = array_pop($positions);
                    //Один раз запоминаем id темпового каталога
                    if ($temp_id === null) {
                        $temp_id = $position['temp_id'];
                    }
                    //Собираем id дублей которые удалим
                    $ids[] = $position['id'];
                }
            }
            //Удаляем все
            if ($temp_id !== null && !empty($ids)) {
                CatalogTempContent::deleteAll(['AND',
                    'temp_id' => $temp_id,
                    ['in', 'id', array_values($ids)]
                ]);
            }
            //Все ок!
            $transaction->commit();
            return ['products' => $this->getGoodsInTempCatalog($request)];
        } catch (\Exception $e) {
            if ($transaction->getIsActive()) {
                $transaction->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Список товаров в каталоге
     *
     * @param $request
     * @return array
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function getGoodsInCatalog($request)
    {
        $page = (isset($request['pagination']['page']) ? $request['pagination']['page'] : 1);
        $pageSize = (isset($request['pagination']['page_size']) ? $request['pagination']['page_size'] : 12);

        $this->validateRequest($request, ['vendor_id']);

        $catalog = $this->getPersonalCatalog($request['vendor_id'], $this->user->organization, true);

        if (empty($catalog)) {
            throw new BadRequestHttpException(\Yii::t('api_web', "catalog_not_found", ['ru'=>'Каталог не найден']));
        }

        $catalogs = explode(',', $this->user->organization->getCatalogs());
        if (!in_array($catalog->id, $catalogs)) {
            throw new BadRequestHttpException(\Yii::t('api_web', "this_is_not_your_catalog", ['ru'=>'Это не Ваш каталог']));
        }

        $selectFields = [
            'cbg.id as product_id',
            'cbg.article as article',
            'cbg.product as name',
            'cbg.ed as ed',
            "{$catalog->currency_id} as `currency_id`",
            "'{$catalog->currency->symbol}' as `currency`"
        ];

        $query = (new Query())->select($selectFields);
        if ($catalog->type == Catalog::BASE_CATALOG) {
            $query->addSelect(['cbg.price as price']);
            $query->from(CatalogBaseGoods::tableName() . ' as cbg');
            $query->where(['cbg.cat_id' => $catalog->id]);
        } else {
            $query->addSelect(['cg.price as price']);
            $query->from(CatalogGoods::tableName() . ' as cg');
            $query->innerJoin(CatalogBaseGoods::tableName() . ' as cbg', 'cg.base_goods_id = cbg.id');
            $query->where(['cg.cat_id' => $catalog->id]);
        }
        $query->andWhere(['cbg.deleted' => CatalogBaseGoods::DELETED_OFF]);

        $dataProvider = new ArrayDataProvider([
            'allModels' => $query->all()
        ]);

        $pagination = new Pagination();
        $pagination->setPage($page - 1);
        $pagination->setPageSize($pageSize);
        $dataProvider->setPagination($pagination);

        $result = $dataProvider->models;
        if (!empty($result)) {
            foreach ($result as &$item) {
                $item['product_id'] = (int)$item['product_id'];
                $item['price'] = round($item['price'], 2);
                $item['currency_id'] = (int)$item['currency_id'];
            }
        }

        return [
            'result'     => array_values($result),
            'pagination' => [
                'page'       => ($dataProvider->pagination->page + 1),
                'page_size'  => $dataProvider->pagination->pageSize,
                'total_page' => ceil($dataProvider->totalCount / $pageSize)
            ]
        ];
    }

    /**
     * Список товаров в временном каталоге
     *
     * @param $request
     * @return array
     * @throws \yii\base\InvalidArgumentException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     * @throws \yii\web\BadRequestHttpException
     */
    public function getGoodsInTempCatalog($request)
    {
        $page = (isset($request['pagination']['page']) ? $request['pagination']['page'] : 1);
        $pageSize = (isset($request['pagination']['page_size']) ? $request['pagination']['page_size'] : 12);

        $this->validateRequest($request, ['vendor_id']);

        $catalog = $this->container->get('CatalogWebApi')->getPersonalCatalog($request['vendor_id'], $this->user->organization, true);
        if (!$catalog) {
            throw new BadRequestHttpException(\Yii::t('api_web', "catalog_not_found", ['ru'=>'Каталог не найден']));
        }
        $tempCatalog = CatalogTemp::findOne(['cat_id' => $catalog->id, 'user_id' => $this->user->id]);
        if (empty($tempCatalog)) {
            throw new BadRequestHttpException(\Yii::t('api_web', "catalog_temp_not_found", ['ru'=>'Временный каталог не найден']));
        }
        $tempContent = CatalogTempContent::find()->where(['temp_id' => $tempCatalog->id]);
        $dataProvider = new ActiveDataProvider([
            'query' => $tempContent
        ]);

        $pagination = new Pagination();
        $pagination->setPage($page - 1);
        $pagination->setPageSize($pageSize);
        $dataProvider->setPagination($pagination);

        $result = $dataProvider->models;

        return [
            'result'     => $result,
            'pagination' => [
                'page'       => ($dataProvider->pagination->page + 1),
                'page_size'  => $dataProvider->pagination->pageSize,
                'total_page' => ceil($dataProvider->totalCount / $pageSize)
            ]
        ];
    }

    /**
     * Список товаров в каталоге
     *
     * @param $request
     * @return array
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function setCurrencyForPersonalCatalog($request)
    {
        $this->validateRequest($request, ['vendor_id', 'currency_id']);

        $catalog = $this->getPersonalCatalog($request['vendor_id'], $this->user->organization, true);

        if (empty($catalog)) {
            throw new BadRequestHttpException(\Yii::t('api_web', "catalog_not_found", ['ru'=>'Каталог не найден']));
        }
        $catalog->currency_id = (int)$request['currency_id'];
        $catalog->save();

        return [
            'result' => true
        ];
    }

    /**
     * @param array $row
     * @return array
     */
    private function prepareTempDuplicatePosition(array $row)
    {
        return [
            "id"      => (int)$row['id'],
            "temp_id" => (int)$row['temp_id'],
            "article" => $row['article'],
            "product" => $row['product'],
            "price"   => round($row['price'], 2),
            "units"   => round($row['units'], 3),
            "note"    => $row['note'],
            "ed"      => $row['ed'],
            "CountOf" => (int)$row['CountOf'] ?? 1
        ];
    }

    /**
     * @param int          $vendorID
     * @param Organization $restOrganization
     * @param bool         $kostilForInvitedVendor //todo_refactoring
     * @return Catalog|null|static
     * @throws ValidationException
     */
    public function getPersonalCatalog(int $vendorID, Organization $restOrganization, $kostilForInvitedVendor = false)
    {
        $relQuery = RelationSuppRest::find()
            ->where([
                'supp_org_id' => $vendorID,
                'rest_org_id' => $restOrganization->id,
                'status' => RelationSuppRest::CATALOG_STATUS_ON,
                'deleted' => 0,
            ]);
        if (!$kostilForInvitedVendor) {
            $relQuery->andWhere([">", "cat_id", 0]);
        }
        $rel = $relQuery->one();

        if (!isset($rel->cat_id) || $rel->cat_id == 0) {
            $catalog = new Catalog();
            $catalog->type = Catalog::CATALOG;
            $catalog->supp_org_id = $vendorID;
            $catalog->name = $restOrganization->name . ' ' . date('d.m.Y');
            $catalog->status = Catalog::STATUS_ON;
            $catalog->currency_id = 1;
            $mainCatalog = Catalog::findOne(['supp_org_id' => $vendorID, 'type' => Catalog::BASE_CATALOG]);
            if ($mainCatalog) {
                $catalog->currency_id = $mainCatalog->currency_id;
                $catalog->main_index = $mainCatalog->main_index;
                $catalog->mapping = $mainCatalog->mapping;
                $catalog->index_column = $mainCatalog->index_column;
            }
            if (!$catalog->save()) {
                throw new ValidationException($catalog->getFirstErrors());
            }
            if ($rel) {
                $rel->cat_id = $catalog->id;
                if (!$rel->save()) {
                    throw new ValidationException($rel->getFirstErrors());
                }
            }
        } else {
            $catalog = Catalog::findOne(['id' => $rel->cat_id]);
        }
        if (!$rel) {
            $rel = new RelationSuppRest();
            $rel->rest_org_id = $restOrganization->id;
            $rel->supp_org_id = $vendorID;
            $rel->cat_id = $catalog->id;
            $rel->invite = 1;
            $rel->status = 1;
            $rel->deleted = 0;
            if (!$rel->save()) {
                throw new ValidationException($rel->getFirstErrors());
            }
        }
        return $catalog;
    }
}
